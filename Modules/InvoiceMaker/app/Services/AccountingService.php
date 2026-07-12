<?php

namespace Modules\InvoiceMaker\Services;

use Illuminate\Support\Facades\DB;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Models\Profile;

class AccountingService
{
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        string $method,
        string $date,
        ?string $notes = null,
    ): Payment {
        return DB::transaction(function () use ($invoice, $amount, $method, $date, $notes): Payment {
            $payment = $invoice->payments()->create([
                'team_id' => $invoice->team_id,
                'amount' => $amount,
                'method' => $method,
                'date' => $date,
                'notes' => $notes,
            ]);
            $amountPaid = min((float) $invoice->grand_total, (float) $invoice->amount_paid + $amount);
            $amountDue = max(0, (float) $invoice->grand_total - $amountPaid);

            $invoice->update([
                'amount_paid' => $amountPaid,
                'amount_due' => $amountDue,
                'status' => $amountDue <= 0 ? Invoice::STATUS_PAID : Invoice::STATUS_SENT,
            ]);

            $invoice->deductInventory();

            CashBookEntry::create([
                'team_id' => $invoice->team_id,
                'booking_number' => $this->nextBookingNumber($invoice->team_id),
                'reference_number' => $invoice->invoice_number,
                'date' => $date,
                'document_date' => $invoice->invoice_date,
                'amount' => $amount,
                'type' => 'income',
                'source' => $method,
                'partner_name' => $invoice->client->company_name ?: $invoice->client->name,
                'description' => $notes ?: __('Payment for :number', ['number' => $invoice->invoice_number]),
                'invoice_id' => $invoice->id,
            ]);

            return $payment;
        });
    }

    public function createExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data): Expense {
            $expense = Expense::create($data);

            CashBookEntry::create([
                'team_id' => $expense->team_id,
                'booking_number' => $this->nextBookingNumber($expense->team_id),
                'reference_number' => $expense->reference_number,
                'date' => $expense->date,
                'document_date' => $expense->date,
                'amount' => $expense->amount,
                'type' => 'expense',
                'source' => 'expense',
                'partner_name' => $expense->partner_name,
                'description' => $expense->description,
                'category_id' => $expense->category_id,
                'expense_id' => $expense->id,
            ]);

            return $expense;
        });
    }

    public function nextBookingNumber(int $teamId): string
    {
        return DB::transaction(function () use ($teamId): string {
            $profile = Profile::withoutGlobalScopes()
                ->where('team_id', $teamId)
                ->lockForUpdate()
                ->firstOrFail();
            $number = sprintf(
                '%s-%04d-%d',
                $profile->booking_number_prefix,
                $profile->booking_number_next,
                now()->year,
            );
            $profile->increment('booking_number_next');

            return $number;
        });
    }
}
