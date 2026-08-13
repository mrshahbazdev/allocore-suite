<?php

namespace Modules\InvoiceMaker\Mail;

use App\Mail\TemplatedMailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\URL;
use Modules\InvoiceMaker\Models\Invoice;

class InvoiceMail extends TemplatedMailable
{
    public Invoice $invoice;

    public ?string $pdfContent;

    public string $customSubject;

    public string $customBody;

    public function __construct(
        Invoice $invoice,
        ?string $pdfContent = null,
        string $customSubject = '',
        string $customBody = '',
    ) {
        $this->invoice = $invoice;
        $this->pdfContent = $pdfContent;
        $this->customSubject = $customSubject ?: __('Invoice :number from :company', [
            'number' => $invoice->invoice_number,
            'company' => $invoice->profile?->name ?? config('app.name'),
        ]);
        $this->customBody = $customBody;
    }

    public function templateTool(): string
    {
        return 'invoicemaker';
    }

    public function templateKey(): string
    {
        return 'invoice';
    }

    public function templateVariables(): array
    {
        return [
            'invoiceNumber' => $this->invoice->invoice_number,
            'amountDue' => $this->invoice->amount_due,
            'currencySymbol' => $this->invoice->currency_symbol,
            'dueDate' => $this->invoice->due_date?->format('d M Y'),
            'url' => URL::signedRoute('invoicemaker.public.show', ['uuid' => $this->invoice->uuid], now()->addDays(30)),
            'downloadUrl' => URL::signedRoute('invoicemaker.public.download', ['uuid' => $this->invoice->uuid], now()->addDays(30)),
            'subjectLine' => $this->customSubject,
            'message' => $this->customBody,
            'appName' => config('app.name'),
        ];
    }

    protected function defaultSubject(): string
    {
        return $this->customSubject;
    }

    protected function defaultContent(): Content
    {
        return new Content(
            markdown: 'invoicemaker::emails.invoice',
            with: $this->templateVariables(),
        );
    }

    public function attachments(): array
    {
        if ($this->pdfContent !== null && $this->pdfContent !== '') {
            return [
                Attachment::fromData(fn () => $this->pdfContent, $this->invoice->invoice_number.'.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
