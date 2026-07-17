<?php

namespace Modules\InvoiceMaker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\InvoiceMaker\Mail\InvoiceMail;
use Modules\InvoiceMaker\Models\Invoice;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Invoice $invoice, public bool $isReminder = false) {}

    public function handle(): void
    {
        $client = $this->invoice->client;

        if (! $client?->email) {
            return;
        }

        $subject = $this->isReminder
            ? __('Reminder: Invoice :number', ['number' => $this->invoice->invoice_number])
            : __('Invoice :number from :company', [
                'number' => $this->invoice->invoice_number,
                'company' => $this->invoice->profile?->company_name ?? config('app.name'),
            ]);

        Mail::to($client->email)->send(new InvoiceMail($this->invoice, $subject));

        if ($this->isReminder) {
            $this->invoice->update(['last_reminder_sent_at' => now()]);
        } else {
            $this->invoice->update(['status' => Invoice::STATUS_SENT, 'sent_at' => now()]);
        }
    }
}
