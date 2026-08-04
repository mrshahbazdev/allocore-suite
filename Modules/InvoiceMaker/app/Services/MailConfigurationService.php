<?php

namespace Modules\InvoiceMaker\Services;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class MailConfigurationService
{
    /**
     * Configure and return a mailer instance for the given business.
     */
    public static function getMailer($business)
    {
        if (! $business->hasCustomSmtp()) {
            return Mail::mailer();
        }

        $config = [
            'transport' => 'smtp',
            'host' => $business->smtp_host,
            'port' => $business->smtp_port,
            'encryption' => $business->smtp_encryption,
            'username' => $business->smtp_username,
            'password' => $business->smtp_password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
            'verify_peer' => $business->smtp_verify_ssl ?? true,
            'verify_peer_name' => $business->smtp_verify_ssl ?? true,
        ];

        // For Laravel 10+, we can use Mail::build() to create a temporary mailer
        return Mail::build($config);
    }

    /**
     * Get the transport instance directly for testing.
     */
    public static function getTransport(array $smtpData)
    {
        return new EsmtpTransport(
            $smtpData['host'],
            $smtpData['port'],
            $smtpData['encryption'] === 'tls',
            null,
            null
        );
    }
}
