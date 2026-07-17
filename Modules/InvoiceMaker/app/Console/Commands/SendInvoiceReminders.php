<?php

namespace Modules\InvoiceMaker\Console\Commands;

use Illuminate\Console\Command;
use Modules\InvoiceMaker\Jobs\SendInvoiceJob;
use Modules\InvoiceMaker\Models\Invoice;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoicemaker:send-reminders';

    protected $description = 'Send reminders for unpaid overdue invoices';

    public function handle(): int
    {
        Invoice::withoutGlobalScopes()
            ->with('profile')
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_OVERDUE])
            ->where('amount_due', '>', 0)
            ->whereDate('due_date', '<', today())
            ->where(function ($query) {
                $query->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<=', now()->subDays(7));
            })
            ->chunkById(100, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    SendInvoiceJob::dispatch($invoice, true);
                }
            });

        return self::SUCCESS;
    }
}
