<?php

namespace Modules\InvoiceMaker\Services;

use Illuminate\Support\Facades\URL;
use Modules\InvoiceMaker\Models\EmailTemplate;
use Modules\InvoiceMaker\Models\Invoice;

class EmailTemplateService
{
    /**
     * Get the parsed subject and body for an invoice based on its type.
     *
     * @param  string  $type  enum(invoice, reminder, receipt)
     * @return array ['subject' => string, 'body' => string]
     */
    public function getParsedTemplate(Invoice $invoice, string $type = 'invoice'): array
    {
        $business = $invoice->business;
        $client = $invoice->client;

        // Try to get a default template from DB
        $template = EmailTemplate::where('team_id', $business->team_id)
            ->where('type', $type)
            ->where('is_default', true)
            ->first();

        // Fallback to hardcoded default if no DB template exists
        if (! $template) {
            $lang = $client->language ?? 'en';
            $subjectTemplate = $this->getHardcodedSubject($lang, $type);
            $bodyTemplate = $this->getHardcodedBody($lang, $type);
        } else {
            $subjectTemplate = $template->subject;
            $bodyTemplate = $template->body;
        }

        // Parse variables
        $placeholders = $this->getPlaceholders($invoice);

        $subject = str_replace(array_keys($placeholders), array_values($placeholders), $subjectTemplate);
        $body = str_replace(array_keys($placeholders), array_values($placeholders), $bodyTemplate);

        return [
            'subject' => $subject,
            'body' => nl2br($body), // Convert newlines to HTML br for emailing
        ];
    }

    public function getPlaceholders(Invoice $invoice): array
    {
        $client = $invoice->client;
        $business = $invoice->business;

        return [
            '[client_name]' => $client->name,
            '[business_name]' => $business->name,
            '[invoice_number]' => $invoice->invoice_number,
            '[amount_due]' => $invoice->currency_symbol.number_format($invoice->amount_due, 2),
            '[total_amount]' => $invoice->currency_symbol.number_format($invoice->grand_total, 2),
            '[due_date]' => $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '',
            '[invoice_link]' => URL::signedRoute('invoicemaker.public.show', ['uuid' => $invoice->uuid]),
            // For backwards compatibility with old `{variable}` format in manual sending
            '{client_name}' => $client->name,
            '{business_name}' => $business->name,
            '{invoice_number}' => $invoice->invoice_number,
            '{amount_due}' => $invoice->currency_symbol.number_format($invoice->amount_due, 2),
            '{total_amount}' => $invoice->currency_symbol.number_format($invoice->grand_total, 2),
            '{due_date}' => $invoice->due_date ? $invoice->due_date->format('d.m.Y') : '',
            '{public_link}' => URL::signedRoute('invoicemaker.public.show', ['uuid' => $invoice->uuid]),
        ];
    }

    private function getHardcodedSubject(string $lang, string $type): string
    {
        // For simplicity, returning English defaults for now if type falls back.
        // The original logic had more translations, which can be adapted if needed.
        if ($type === 'reminder') {
            return 'Payment Reminder: Invoice [invoice_number] from [business_name]';
        }

        if ($type === 'receipt') {
            return 'Payment Receipt: Invoice [invoice_number] from [business_name]';
        }

        return 'Invoice [invoice_number] from [business_name]';
    }

    private function getHardcodedBody(string $lang, string $type): string
    {
        if ($type === 'reminder') {
            return "Hello [client_name],\n\nThis is a friendly reminder that your invoice [invoice_number] for [amount_due] was due on [due_date].\n\nYou can view and pay your invoice securely online at:\n[invoice_link]\n\nThank you,\n[business_name]";
        }

        if ($type === 'receipt') {
            return "Hello [client_name],\n\nThank you for your payment! Your invoice [invoice_number] has been marked as paid.\n\nYou can view your updated invoice online:\n[invoice_link]\n\nThank you for your business,\n[business_name]";
        }

        return "Hello [client_name],\n\nHere is your requested invoice [invoice_number].\n\nThe outstanding amount of [amount_due] is due by [due_date].\n\nYou can view and download your invoice online here:\n[invoice_link]\n\nThank you for your business!\n\nBest regards,\n[business_name]";
    }
}
