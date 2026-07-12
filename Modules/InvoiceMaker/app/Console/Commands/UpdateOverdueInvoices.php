<?php

namespace Modules\InvoiceMaker\Console\Commands;

use Illuminate\Console\Command;
use Modules\InvoiceMaker\Models\Invoice;

class UpdateOverdueInvoices extends Command
{
    protected $signature = 'invoicemaker:update-overdue';

    protected $description = 'Mark overdue invoices and apply configured late fees';

    public function handle(): int
    {
        Invoice::withoutGlobalScopes()
            ->with('profile')
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('status', Invoice::STATUS_SENT)
            ->whereDate('due_date', '<', today())
            ->chunkById(100, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    $lateFee = (float) $invoice->late_fee_amount;

                    if ($lateFee <= 0 && (float) $invoice->profile?->late_fee_percentage > 0) {
                        $lateFee = round(
                            (float) $invoice->grand_total
                            * ((float) $invoice->profile->late_fee_percentage / 100),
                            2,
                        );
                    }

                    $invoice->update([
                        'status' => Invoice::STATUS_OVERDUE,
                        'late_fee_amount' => $lateFee,
                        'grand_total' => (float) $invoice->grand_total + $lateFee,
                        'amount_due' => (float) $invoice->amount_due + $lateFee,
                    ]);
                }
            });

        return self::SUCCESS;
    }
}
