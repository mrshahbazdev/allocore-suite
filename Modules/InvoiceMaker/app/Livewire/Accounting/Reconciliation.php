<?php

namespace Modules\InvoiceMaker\Livewire\Accounting;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Expense;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Reconciliation extends Component
{
    use WithFileUploads;

    public $step = 1; // 1: Upload, 2: Map, 3: Review

    // Step 1: Upload
    public $csvFile;

    public $csvHeaders = [];

    public $csvPreview = [];

    public $parsedData = []; // Full parsed raw array

    // Step 2: Mapping
    public $mapDate = '';

    public $mapDescription = '';

    public $mapAmount = '';

    public $availableHeaders = [];

    // Step 3: Review
    // Will be an array of: [ 'id' => int, 'date' => date, 'description' => string, 'amount' => float, 'matched_type' => 'invoice'|'expense'|'misc', 'matched_id' => int|null, 'status' => 'exact'|'manual'|'unmatched', 'selected' => bool ]
    public $reconciliationRows = [];

    protected function rules()
    {
        if ($this->step === 1) {
            return [
                'csvFile' => 'required|file|mimes:csv,txt|max:5120',
            ];
        }

        if ($this->step === 2) {
            return [
                'mapDate' => 'required|string',
                'mapDescription' => 'required|string',
                'mapAmount' => 'required|string',
            ];
        }

        return [];
    }

    public function processUpload()
    {
        $this->validate();

        $path = $this->csvFile->getRealPath();
        $data = array_map('str_getcsv', file($path));

        if (count($data) < 2) {
            $this->addError('csvFile', 'The CSV file is empty or does not contain a header row.');

            return;
        }

        // Parse headers (row 0)
        $this->csvHeaders = array_map('trim', $data[0]);

        // Validate unique headers just in case
        $this->availableHeaders = $this->csvHeaders;

        // Take a small preview of data
        $this->csvPreview = array_slice($data, 1, 3);

        // Store the rest for mapping phase
        $this->parsedData = array_slice($data, 1);

        $this->step = 2;
    }

    public function processMapping()
    {
        $this->validate();

        $dateIndex = array_search($this->mapDate, $this->csvHeaders);
        $descIndex = array_search($this->mapDescription, $this->csvHeaders);
        $amountIndex = array_search($this->mapAmount, $this->csvHeaders);

        if ($dateIndex === false || $descIndex === false || $amountIndex === false) {
            session()->flash('error', 'Invalid column mapping selected.');

            return;
        }

        $businessId = app(InvoiceMakerContext::class)->profile()->team_id;

        // Pre-load all unpaid/partially paid invoices for fast matching
        $openInvoices = Invoice::where('team_id', $businessId)
            ->whereIn('status', ['sent', 'viewed', 'overdue', 'partial'])
            ->get();

        // Pre-load expenses that don't have a linked cash book entry yet
        // A naive way is to load expenses, but wait, do expenses link directly to cash book?
        // Yes, CashBookEntry has `expense_id`.
        $linkedExpenseIds = CashBookEntry::where('team_id', $businessId)
            ->whereNotNull('expense_id')
            ->pluck('expense_id')->toArray();

        $openExpenses = Expense::where('team_id', $businessId)
            ->whereNotIn('id', $linkedExpenseIds)
            ->get();

        $this->reconciliationRows = [];

        foreach ($this->parsedData as $index => $row) {
            // Ensure row has enough columns
            if (count($row) <= max($dateIndex, $descIndex, $amountIndex)) {
                continue;
            }

            $rawDate = trim($row[$dateIndex]);
            $rawDesc = trim($row[$descIndex]);
            $rawAmount = trim($row[$amountIndex]);

            // Try to parse amount (handle European formats optionally, but standard float for MVP)
            $rawAmount = str_replace(',', '', $rawAmount); // Strip commas
            $amount = (float) $rawAmount;

            if ($amount == 0) {
                continue;
            }

            try {
                // Carbon parse dates, can fail if format is weird
                $dateObj = Carbon::parse($rawDate);
            } catch (Exception $e) {
                // Fallback to today if unparseable
                $dateObj = Carbon::now();
            }

            $matchedType = 'misc';
            $matchedId = null;
            $status = 'unmatched';
            $matchConfidence = 0; // The higher the better

            // AUTO-MATCHING ENGINE
            if ($amount > 0) {
                // Income -> Look for Invoices
                foreach ($openInvoices as $inv) {
                    $score = 0;

                    // Does the description contain the invoice number?
                    if (str_contains(strtolower($rawDesc), strtolower($inv->invoice_number))) {
                        $score += 50;
                    }

                    // Does the amount exactly equal the grand total?
                    if (abs($inv->grand_total - $amount) < 0.01) {
                        $score += 50;
                    }

                    // Does the amount exactly equal the balance remaining?
                    $paid = $inv->payments()->sum('amount');
                    $balance = $inv->grand_total - $paid;
                    if (abs($balance - $amount) < 0.01) {
                        $score += 50;
                    }

                    if ($score >= 50 && $score > $matchConfidence) {
                        $matchedType = 'invoice';
                        $matchedId = $inv->id;
                        $matchConfidence = $score;
                        $status = $score >= 100 ? 'exact' : 'partial_match';
                    }
                }
            } else {
                // Expense -> Look for Expenses
                $absAmount = abs($amount);
                foreach ($openExpenses as $exp) {
                    $score = 0;

                    if ($exp->reference_number && str_contains(strtolower($rawDesc), strtolower($exp->reference_number))) {
                        $score += 50;
                    }

                    if (abs($exp->amount - $absAmount) < 0.01) {
                        $score += 50;
                    }

                    if ($score >= 50 && $score > $matchConfidence) {
                        $matchedType = 'expense';
                        $matchedId = $exp->id;
                        $matchConfidence = $score;
                        $status = $score >= 100 ? 'exact' : 'partial_match';
                    }
                }
            }

            $this->reconciliationRows[] = [
                'id' => $index,
                'raw_date' => $dateObj->format('Y-m-d'),
                'description' => $rawDesc,
                'amount' => $amount,
                'matched_type' => $matchedType, // 'invoice', 'expense', 'misc'
                'matched_id' => $matchedId,
                'status' => $status,
                'selected' => true, // Checkbox to import this row
            ];
        }

        $this->step = 3;
    }

    public function finalizeReconciliation()
    {
        $businessId = app(InvoiceMakerContext::class)->profile()->team_id;
        $processedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($this->reconciliationRows as $row) {
                if (! $row['selected']) {
                    continue;
                }

                $amt = (float) $row['amount'];
                $isIncome = $amt > 0;
                $absAmount = abs($amt);

                // 1. Matched Invoice
                if ($row['matched_type'] === 'invoice' && $row['matched_id']) {
                    $invoice = Invoice::where('team_id', $businessId)->find($row['matched_id']);
                    if ($invoice) {
                        // Create Payment
                        Payment::create([
                            'invoice_id' => $invoice->id,
                            'amount' => $absAmount,
                            'date' => $row['raw_date'],
                            'method' => Payment::METHOD_BANK_TRANSFER,
                            'notes' => 'Imported via CSV: '.$row['description'],
                        ]);

                        // Determine new status
                        $totalPaid = $invoice->payments()->sum('amount');
                        if ($totalPaid >= $invoice->grand_total - 0.01) {
                            $invoice->update(['status' => 'paid']);
                        } else {
                            $invoice->update(['status' => 'partial']);
                        }

                        // Create Cash Book Entry
                        CashBookEntry::create([
                            'team_id' => $businessId,
                            'invoice_id' => $invoice->id,
                            'date' => $row['raw_date'],
                            'document_date' => $row['raw_date'],
                            'amount' => $absAmount,
                            'type' => 'income',
                            'source' => 'bank_import',
                            'description' => $row['description'],
                            'partner_name' => $invoice->client->name ?? 'Unknown',
                        ]);
                    }
                }
                // 2. Matched Expense
                elseif ($row['matched_type'] === 'expense' && $row['matched_id']) {
                    $expense = Expense::where('team_id', $businessId)->find($row['matched_id']);
                    if ($expense) {
                        CashBookEntry::create([
                            'team_id' => $businessId,
                            'expense_id' => $expense->id,
                            'date' => $row['raw_date'],
                            'document_date' => $row['raw_date'],
                            'amount' => $absAmount,
                            'type' => 'expense',
                            'source' => 'bank_import',
                            'description' => $row['description'],
                            'partner_name' => $expense->partner_name,
                            'category_id' => $expense->category_id,
                        ]);
                    }
                }
                // 3. Misc Entry
                else {
                    CashBookEntry::create([
                        'team_id' => $businessId,
                        'date' => $row['raw_date'],
                        'document_date' => $row['raw_date'],
                        'amount' => $absAmount,
                        'type' => $isIncome ? 'income' : 'expense',
                        'source' => 'bank_import',
                        'description' => $row['description'],
                    ]);
                }

                $processedCount++;
            }

            DB::commit();

            session()->flash('message', "Successfully reconciled {$processedCount} transactions.");

            return redirect()->route('accounting.cash-book');

        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error during reconciliation: '.$e->getMessage());
            throw $e;
        }
    }

    public function cancel()
    {
        return redirect()->route('accounting.cash-book');
    }

    public function editRowMatch($index, $type, $id = null)
    {
        // A helper method if the user manually overrides a matched row in Step 3
        if (isset($this->reconciliationRows[$index])) {
            $this->reconciliationRows[$index]['matched_type'] = $type;
            $this->reconciliationRows[$index]['matched_id'] = $id ?: null;
            $this->reconciliationRows[$index]['status'] = 'manual';
        }
    }

    public function render()
    {
        $businessId = app(InvoiceMakerContext::class)->profile()->team_id;

        $openInvoices = Invoice::where('team_id', $businessId)
            ->whereIn('status', ['sent', 'viewed', 'overdue', 'partial'])
            ->get();

        $linkedExpenseIds = CashBookEntry::where('team_id', $businessId)
            ->whereNotNull('expense_id')
            ->pluck('expense_id')->toArray();

        $openExpenses = Expense::where('team_id', $businessId)
            ->whereNotIn('id', $linkedExpenseIds)
            ->get();

        return view('invoicemaker::livewire.accounting.reconciliation', [
            'openInvoices' => $openInvoices,
            'openExpenses' => $openExpenses,
        ]);
    }
}
