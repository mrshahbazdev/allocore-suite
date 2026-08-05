<?php

namespace Modules\InvoiceMaker\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Modules\InvoiceMaker\Models\Invoice;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

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

    public function build(): self
    {
        $mailable = $this->subject($this->customSubject)
            ->markdown('invoicemaker::emails.invoice')
            ->with([
                'invoice' => $this->invoice,
                'subjectLine' => $this->customSubject,
                'message' => $this->customBody,
                'url' => URL::signedRoute('invoicemaker.public.show', ['uuid' => $this->invoice->uuid], now()->addDays(30)),
                'downloadUrl' => URL::signedRoute('invoicemaker.public.download', ['uuid' => $this->invoice->uuid], now()->addDays(30)),
            ]);

        if ($this->pdfContent !== null && $this->pdfContent !== '') {
            $mailable->attachData(
                $this->pdfContent,
                $this->invoice->invoice_number.'.pdf',
                ['mime' => 'application/pdf'],
            );
        }

        return $mailable;
    }
}
