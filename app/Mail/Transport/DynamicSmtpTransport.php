<?php

namespace App\Mail\Transport;

use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class DynamicSmtpTransport extends AbstractTransport
{
    public function __toString(): string
    {
        return 'dynamic+smtp://default';
    }

    protected function doSend(SentMessage $message): void
    {
        $setting = $this->resolveSetting($message);

        if (! $setting) {
            $this->sendWithFallback($message);

            return;
        }

        $transport = $this->buildTransport($setting->toMailerConfig());

        $this->forward($transport, $message, $setting);
    }

    private function resolveSetting(SentMessage $message): ?MailSetting
    {
        $recipients = $message->getEnvelope()->getRecipients();
        $first = $recipients[0] ?? null;

        if (! $first) {
            return MailSetting::effectiveFor(null);
        }

        $user = User::where('email', $first->getAddress())->first();

        return MailSetting::effectiveFor($user);
    }

    private function buildTransport(array $config): TransportInterface
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

    private function scheme(array $config): string
    {
        if (($config['encryption'] ?? null) === 'ssl' || ($config['port'] ?? null) == 465) {
            return 'smtps';
        }

        return 'smtp';
    }

    private function forward(TransportInterface $transport, SentMessage $message, MailSetting $setting): void
    {
        $email = $message->getOriginalMessage();
        $envelope = $message->getEnvelope();
        $fromAddress = $this->resolveFromAddress($setting);

        if (filled($fromAddress)) {
            $fromName = $this->resolveFromName($setting);
            $from = new Address($fromAddress, $fromName ?? '');
            $envelope->setSender($from);

            if ($email instanceof Email) {
                $email->from($from);
            }
        }

        $transport->send($email, $envelope);
    }

    private function resolveFromAddress(MailSetting $setting): ?string
    {
        $global = MailSetting::query()->global()->first();

        $candidates = array_filter([
            filled($setting->from_address) && ! $this->isPlaceholderAddress($setting->from_address) ? $setting->from_address : null,
            $setting->user_id ? $global?->from_address : null,
            $global?->from_address,
            config('mail.from.address'),
        ]);

        Log::error('DynamicSmtpTransport from candidates', [
            'setting_from' => $setting->from_address,
            'global_from' => $global?->from_address,
            'config_from' => config('mail.from.address'),
            'candidates' => $candidates,
        ]);

        foreach ($candidates as $candidate) {
            if (! $this->isPlaceholderAddress($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveFromName(MailSetting $setting): ?string
    {
        if (filled($setting->from_name)) {
            return $setting->from_name;
        }

        if ($setting->user_id) {
            return MailSetting::query()->global()->first()?->from_name ?: config('mail.from.name');
        }

        return config('mail.from.name');
    }

    private function isPlaceholderAddress(?string $address): bool
    {
        if (empty($address)) {
            return true;
        }

        $address = strtolower($address);

        return str_ends_with($address, '@example.com') || str_contains($address, 'example.com');
    }

    private function sendWithFallback(SentMessage $message): void
    {
        $config = config('mail.mailers.smtp');

        if (! is_array($config) || empty($config['host'])) {
            throw new \RuntimeException('No SMTP configuration found. Please set an admin default mail server or configure MAIL_* environment variables.');
        }

        $setting = new MailSetting([
            'driver' => $config['transport'] ?? 'smtp',
            'host' => $config['host'],
            'port' => $config['port'] ?? 587,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'encryption' => $this->encryptionFromConfig($config),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ]);

        $transport = $this->buildTransport($setting->toMailerConfig());

        $transport->send($message->getMessage(), $message->getEnvelope());
    }

    private function encryptionFromConfig(array $config): string
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
