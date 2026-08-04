<?php

namespace Modules\InvoiceMaker\Livewire\Invoices;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\CashBookEntry;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Payment;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'invoice_date';

    public string $sortDirection = 'desc';

    // Mark as Paid State
    public bool $showPaidModal = false;

    public $selectedInvoiceId;

    public $paymentDate;

    public $paymentSource = 'bank';

    public $paymentDescription = '';

    // Bulk recurring loop state
    public array $selected = [];

    public bool $showBulkRecurringModal = false;

    public string $bulkRecurringFrequency = 'monthly';

    public string $bulkRecurringNextRunDate = '';

    protected const PER_PAGE = 10;

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function markAsUnpaid(int $id): void
    {
        $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($id);
        $invoice->update(['status' => Invoice::STATUS_SENT]);
        session()->flash('message', 'Invoice marked as unpaid.');
    }

    public function reopenInvoice(int $id): void
    {
        $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($id);

        DB::transaction(function () use ($invoice) {
            $invoice->payments()->delete();
            CashBookEntry::where('invoice_id', $invoice->id)->delete();

            $invoice->update([
                'status' => Invoice::STATUS_SENT,
                'amount_paid' => 0,
                'amount_due' => $invoice->grand_total,
            ]);
        });

        session()->flash('message', __('Invoice reopened. Payments and cash book entries have been reversed.'));
    }

    public function cancelPaidInvoice(int $id): void
    {
        $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($id);

        DB::transaction(function () use ($invoice) {
            $invoice->payments()->delete();
            CashBookEntry::where('invoice_id', $invoice->id)->delete();

            $invoice->update([
                'status' => Invoice::STATUS_CANCELLED,
                'amount_paid' => 0,
                'amount_due' => $invoice->grand_total,
            ]);
        });

        session()->flash('message', __('Invoice cancelled and payments reversed.'));
    }

    public function delete(int $id): void
    {
        $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($id);
        $invoice->delete();
        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function openPaidModal(int $id): void
    {
        $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($id);
        $this->selectedInvoiceId = $id;
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentDescription = __('Payment for Invoice').' '.$invoice->invoice_number;
        $this->showPaidModal = true;
    }

    public function closePaidModal(): void
    {
        $this->showPaidModal = false;
        $this->reset(['selectedInvoiceId', 'paymentDate', 'paymentSource', 'paymentDescription']);
    }

    public function markAsPaid(): void
    {
        $this->validate([
            'paymentDate' => 'required|date',
            'paymentSource' => 'required|in:cash,bank',
            'paymentDescription' => 'required|string|max:255',
        ]);

        DB::transaction(function () {
            $invoice = app(InvoiceMakerContext::class)->profile()->invoices()->findOrFail($this->selectedInvoiceId);

            // Update Invoice Status
            $invoice->update([
                'status' => Invoice::STATUS_PAID,
                'amount_paid' => $invoice->grand_total,
                'amount_due' => 0,
            ]);

            // Create Payment History record
            Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => $invoice->grand_total,
                'method' => $this->paymentSource === 'cash' ? 'cash' : 'bank_transfer',
                'date' => $this->paymentDate,
                'notes' => $this->paymentDescription,
            ]);

            // Create Cash Book Entry
            CashBookEntry::create([
                'team_id' => app(InvoiceMakerContext::class)->profile()->team_id,
                'date' => $this->paymentDate,
                'document_date' => $invoice->invoice_date,
                'amount' => $invoice->grand_total,
                'type' => 'income',
                'source' => $this->paymentSource,
                'description' => $this->paymentDescription,
                'partner_name' => $invoice->client->company_name ?? $invoice->client->name,
                'reference_number' => $invoice->invoice_number,
                'invoice_id' => $invoice->id,
            ]);
        });

        $this->closePaidModal();
        session()->flash('message', __('Invoice marked as paid and Cash Book entry created.'));
    }

    public function updatedSearch(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function updatedSortDirection(): void
    {
        $this->selected = [];
        $this->resetPage();
    }

    public function toggleSelectAll(): void
    {
        $visibleIds = $this->getBaseQuery()->paginate(self::PER_PAGE)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $selected = collect($this->selected);

        if ($selected->intersect($visibleIds)->count() === count($visibleIds)) {
            $this->selected = $selected->diff($visibleIds)->values()->all();
        } else {
            $this->selected = $selected->merge($visibleIds)->unique()->values()->all();
        }
    }

    public function selectAllMatching(): void
    {
        $this->selected = $this->getBaseQuery()->select('id')->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function removeFromRecurringLoop(): void
    {
        if (empty($this->selected)) {
            session()->flash('error', __('Please select at least one invoice.'));

            return;
        }

        $count = app(InvoiceMakerContext::class)->profile()->invoices()
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereIn('id', $this->selected)
            ->where('is_recurring', true)
            ->update([
                'is_recurring' => false,
                'recurring_frequency' => null,
                'next_run_date' => null,
                'last_run_date' => null,
            ]);

        session()->flash('message', __(':count invoice(s) removed from the recurring loop.', ['count' => $count]));
        $this->selected = [];
    }

    public function openBulkRecurringModal(): void
    {
        if (empty($this->selected)) {
            session()->flash('error', __('Please select at least one invoice.'));

            return;
        }

        $this->bulkRecurringFrequency = 'monthly';
        $this->bulkRecurringNextRunDate = now()->toDateString();
        $this->showBulkRecurringModal = true;
    }

    public function closeBulkRecurringModal(): void
    {
        $this->showBulkRecurringModal = false;
        $this->reset(['bulkRecurringFrequency', 'bulkRecurringNextRunDate']);
    }

    public function insertIntoRecurringLoop(): void
    {
        if (empty($this->selected)) {
            session()->flash('error', __('Please select at least one invoice.'));

            return;
        }

        $this->validate([
            'bulkRecurringFrequency' => 'required|in:weekly,monthly,quarterly,yearly',
            'bulkRecurringNextRunDate' => 'required|date',
        ]);

        $count = app(InvoiceMakerContext::class)->profile()->invoices()
            ->where('type', Invoice::TYPE_INVOICE)
            ->whereIn('id', $this->selected)
            ->update([
                'is_recurring' => true,
                'recurring_frequency' => $this->bulkRecurringFrequency,
                'next_run_date' => $this->bulkRecurringNextRunDate,
            ]);

        $this->closeBulkRecurringModal();
        $this->selected = [];
        session()->flash('message', __(':count invoice(s) inserted into the recurring loop.', ['count' => $count]));
    }

    protected function getBaseQuery()
    {
        $query = app(InvoiceMakerContext::class)->profile()->invoices()->where('type', Invoice::TYPE_INVOICE)->with('client')
            ->withCount(['emailLogs as emails_sent_count' => function ($q) {
                $q->where('status', 'sent');
            }]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', function ($cq) {
                        $cq->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->orderBy($this->sortBy, $this->sortDirection);
    }

    public function render()
    {
        $invoices = $this->getBaseQuery()->paginate(self::PER_PAGE);

        return view('invoicemaker::livewire.invoices.index', compact('invoices'));
    }
}
