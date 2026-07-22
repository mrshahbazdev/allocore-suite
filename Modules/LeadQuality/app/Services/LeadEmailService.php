<?php

namespace Modules\LeadQuality\Services;

use App\Models\MailSetting;
use App\Models\User;
use Modules\LeadQuality\Models\Contact;
use Modules\LeadQuality\Models\EmailAccount;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class LeadEmailService
{
    public function send(Contact $contact, string $subject, string $body, ?User $sender = null): array
    {
        $from = $this->resolveFrom($contact, $sender);

        $email = (new Email)
            ->from(new Address($from['address'], $from['name']))
            ->to(new Address($contact->email, $contact->name ?? ''))
            ->subject($subject)
            ->html($body);

        $transport = $this->resolveTransport($contact, $sender);

        try {
            $transport->send($email);

            return ['success' => true, 'message' => 'Email sent.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function resolveFrom(Contact $contact, ?User $sender): array
    {
        $account = $this->activeEmailAccount($contact);
        if ($account) {
            return [
                'address' => $account->email_address,
                'name' => $sender?->name ?? config('app.name'),
            ];
        }

        $setting = MailSetting::effectiveFor($sender);
        if ($setting) {
            return [
                'address' => $setting->from_address ?: $setting->username,
                'name' => $setting->displayName(),
            ];
        }

        return [
            'address' => config('mail.from.address', 'noreply@example.com'),
            'name' => config('mail.from.name', config('app.name')),
        ];
    }

    protected function resolveTransport(Contact $contact, ?User $sender): TransportInterface
    {
        $account = $this->activeEmailAccount($contact);
        if ($account) {
            return $this->buildTransport([
                'transport' => 'smtp',
                'host' => $account->smtp_host,
                'port' => $account->smtp_port,
                'encryption' => $account->smtp_encryption,
                'username' => $account->username,
                'password' => $account->password,
                'timeout' => null,
                'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST),
            ]);
        }

        $setting = MailSetting::effectiveFor($sender);
        if ($setting) {
            return $this->buildTransport($setting->toMailerConfig());
        }

        return $this->buildDefaultTransport();
    }

    protected function activeEmailAccount(Contact $contact): ?EmailAccount
    {
        return EmailAccount::query()
            ->where('team_id', $contact->team_id)
            ->where('is_active', true)
            ->whereNotNull('smtp_host')
            ->first();
    }

    protected function buildTransport(array $config): TransportInterface
    {
        $scheme = $this->scheme($config);

        $options = [
            'local_domain' => $config['local_domain'] ?? null,
        ];

        $encryption = $config['encryption'] ?? null;

        if ($scheme === 'smtps') {
            $options['auto_tls'] = '0';
        } elseif ($encryption === 'tls') {
            $options['auto_tls'] = '1';
            $options['require_tls'] = '1';
        } elseif ($encryption === '') {
            $options['auto_tls'] = '0';
        }

        $factory = new EsmtpTransportFactory;

        $dsn = new Dsn(
            $scheme,
            $config['host'],
            $config['username'] ?? null,
            $config['password'] ?? null,
            $config['port'] ?? null,
            $options
        );

        return $factory->create($dsn);
    }

    protected function buildDefaultTransport(): TransportInterface
    {
        $config = config('mail.mailers.smtp');

        if (! is_array($config) || empty($config['host'])) {
            throw new \RuntimeException('No SMTP configuration found. Set admin mail settings or MAIL_* env variables.');
        }

        return $this->buildTransport([
            'transport' => $config['transport'] ?? 'smtp',
            'host' => $config['host'],
            'port' => $config['port'] ?? 587,
            'encryption' => $this->encryptionFromConfig($config),
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'timeout' => null,
            'local_domain' => parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST),
        ]);
    }

    protected function scheme(array $config): string
    {
        if (($config['encryption'] ?? null) === 'ssl' || ($config['port'] ?? null) == 465) {
            return 'smtps';
        }

        return 'smtp';
    }

    protected function encryptionFromConfig(array $config): string
    {
        $scheme = $config['scheme'] ?? null;

        if ($scheme === 'smtps' || ($config['port'] ?? null) == 465) {
            return 'ssl';
        }

        if ($scheme === 'smtp' && isset($config['encryption'])) {
            return $config['encryption'];
        }

        return '';
    }
}
