<?php

namespace Modules\InvoiceMaker\Console\Commands;

use Illuminate\Console\Command;
use Modules\InvoiceMaker\Jobs\SendInvoiceJob;
use Modules\InvoiceMaker\Models\Invoice;

class SendScheduledInvoices extends Command
{
    protected $signature = 'invoicemaker:send-scheduled';

    protected $description = 'Send draft invoices that are scheduled for now or earlier';

    public function handle(): int
    {
        Invoice::withoutGlobalScopes()
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('status', Invoice::STATUS_DRAFT)
            ->whereNotNull('scheduled_send_at')
            ->where('scheduled_send_at', '<=', now())
            ->chunkById(100, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    SendInvoiceJob::dispatch($invoice);
                }
            });

        return self::SUCCESS;
    }
}
