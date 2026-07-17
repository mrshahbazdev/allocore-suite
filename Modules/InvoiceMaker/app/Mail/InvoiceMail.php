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

    public function __construct(
        public Invoice $invoice,
        public string $subjectLine,
        public ?string $message = null,
    ) {}

    public function build(): self
    {
        return $this->subject($this->subjectLine)
            ->markdown('invoicemaker::emails.invoice')
            ->with([
                'url' => URL::signedRoute('invoicemaker.public.show', $this->invoice->uuid, now()->addDays(30)),
                'downloadUrl' => URL::signedRoute('invoicemaker.public.download', $this->invoice->uuid, now()->addDays(30)),
            ]);
    }
}
