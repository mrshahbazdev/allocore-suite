<?php

namespace App\Mail\Transport;

use App\Models\MailSetting;
use App\Models\User;
use Symfony\Component\Mailer\Envelope;
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
        $email = $message->getMessage();
        $envelope = $message->getEnvelope();

        if ($email instanceof Email && filled($setting->from_address)) {
            $from = new Address($setting->from_address, $setting->from_name ?? '');
            $email->from($from);

            $envelope = new Envelope($from, $envelope->getRecipients());
        }

        $transport->send($email, $envelope);
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
