<?php

namespace Modules\InvoiceMaker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Profile;
use Modules\InvoiceMaker\Services\InvoiceNumberService;

class ProcessRecurringInvoices extends Command
{
    protected $signature = 'invoicemaker:process-recurring';

    protected $description = 'Create invoices due from recurring invoice schedules';

    public function handle(InvoiceNumberService $numberService): int
    {
        Invoice::withoutGlobalScopes()
            ->with('items')
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('is_recurring', true)
            ->whereDate('next_run_date', '<=', today())
            ->chunkById(100, function ($invoices) use ($numberService): void {
                foreach ($invoices as $invoice) {
                    DB::transaction(function () use ($invoice, $numberService): void {
                        $profile = Profile::withoutGlobalScopes()
                            ->where('team_id', $invoice->team_id)
                            ->firstOrFail();
                        $copy = $invoice->replicate([
                            'uuid',
                            'invoice_number',
                            'status',
                            'invoice_date',
                            'due_date',
                            'amount_paid',
                            'amount_due',
                            'is_recurring',
                            'recurring_frequency',
                            'next_run_date',
                            'last_run_date',
                            'sent_at',
                            'public_viewed_at',
                            'accepted_at',
                            'revision_requested_at',
                        ]);
                        $copy->invoice_number = $numberService->generate($profile);
                        $copy->status = Invoice::STATUS_DRAFT;
                        $copy->invoice_date = today();
                        $copy->due_date = today()->addDays($profile->payment_terms_days);
                        $copy->amount_paid = 0;
                        $copy->amount_due = $copy->grand_total;
                        $copy->is_recurring = false;
                        $copy->save();

                        foreach ($invoice->items as $item) {
                            $copy->items()->create([
                                ...$item->only([
                                    'product_id',
                                    'description',
                                    'quantity',
                                    'unit_price',
                                    'tax_rate',
                                    'tax_amount',
                                    'discount',
                                    'total',
                                ]),
                                'team_id' => $copy->team_id,
                            ]);
                        }

                        $invoice->update([
                            'last_run_date' => today(),
                            'next_run_date' => $this->nextRunDate($invoice),
                        ]);
                    });
                }
            });

        return self::SUCCESS;
    }

    private function nextRunDate(Invoice $invoice)
    {
        $date = $invoice->next_run_date ?? today();

        return match ($invoice->recurring_frequency) {
            'weekly' => $date->copy()->addWeek(),
            'quarterly' => $date->copy()->addMonths(3),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }
}
