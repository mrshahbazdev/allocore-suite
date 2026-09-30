<?php

namespace Modules\InvoiceMaker\Console\Commands;

use Illuminate\Console\Command;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Services\AllocoreReporter;

class BackfillAllocoreMetrics extends Command
{
    protected $signature = 'allocore:backfill {--dry-run : Nur zählen, nichts senden}';

    protected $description = 'Sendet bestehende InvoiceMaker-Daten als KPI-Events (mit occurred_at) an Allocore Manager';

    public function handle(AllocoreReporter $reporter): int
    {
        $dry = (bool) $this->option('dry-run');
        $push = function (string $type, array $body) use ($reporter, $dry): void {
            if (! $dry) {
                $reporter->push($type, $body);
            }
        };

        $counts = ['invoice_created' => 0, 'invoice_paid' => 0, 'payment_received' => 0, 'expense_created' => 0, 'customer_created' => 0];

        Invoice::withoutGlobalScopes()
            ->where('type', Invoice::TYPE_INVOICE)
            ->chunkById(200, function ($invoices) use (&$counts, $push): void {
                foreach ($invoices as $invoice) {
                    $occurred = (string) ($invoice->invoice_date ?? $invoice->created_at?->toDateString());
                    $push('invoice_created', [
                        'amount' => (float) $invoice->grand_total,
                        'currency' => $invoice->currency,
                        'invoice_id' => $invoice->id,
                        'occurred_at' => $occurred,
                    ]);
                    $counts['invoice_created']++;

                    if ($invoice->status === Invoice::STATUS_PAID) {
                        $push('invoice_paid', [
                            'amount' => (float) $invoice->grand_total,
                            'currency' => $invoice->currency,
                            'invoice_id' => $invoice->id,
                            'occurred_at' => (string) ($invoice->payments()->latest('date')->value('date') ?? $occurred),
                        ]);
                        $counts['invoice_paid']++;
                    }
                }
            });

        Payment::withoutGlobalScopes()->chunkById(200, function ($payments) use (&$counts, $push): void {
            foreach ($payments as $payment) {
                $push('payment_received', [
                    'amount' => (float) $payment->amount,
                    'payment_id' => $payment->id,
                    'occurred_at' => (string) ($payment->date ?? $payment->created_at?->toDateString()),
                ]);
                $counts['payment_received']++;
            }
        });

        Expense::withoutGlobalScopes()->with('accounting_category')->chunkById(200, function ($expenses) use (&$counts, $push): void {
            foreach ($expenses as $expense) {
                $push('expense_created', [
                    'amount' => (float) $expense->amount,
                    'category' => strtolower((string) ($expense->accounting_category?->name ?? $expense->category ?? '')),
                    'expense_id' => $expense->id,
                    'occurred_at' => (string) ($expense->date ?? $expense->created_at?->toDateString()),
                ]);
                $counts['expense_created']++;
            }
        });

        Client::withoutGlobalScopes()->chunkById(200, function ($clients) use (&$counts, $push): void {
            foreach ($clients as $client) {
                $push('customer_created', [
                    'client_id' => $client->id,
                    'occurred_at' => (string) ($client->created_at?->toDateString() ?? ''),
                ]);
                $counts['customer_created']++;
            }
        });

        $this->info(($dry ? '[dry-run] ' : '').'Backfill: '.collect($counts)->map(fn ($n, $k) => "{$k}={$n}")->implode(', '));

        return self::SUCCESS;
    }
}
