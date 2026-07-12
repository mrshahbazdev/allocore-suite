<?php

namespace Modules\InvoiceMaker\Livewire\Documents;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Modules\InvoiceMaker\Services\InvoiceNumberService;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public string $type = Invoice::TYPE_INVOICE;

    public string $search = '';

    public string $status = '';

    public function mount(): void
    {
        $this->type = request()->route('type', Invoice::TYPE_INVOICE);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function delete(Invoice $invoice): void
    {
        abort_unless($invoice->type === $this->type, 404);

        if ($invoice->payments()->exists()) {
            session()->flash('warning', __('Documents with payments cannot be deleted.'));

            return;
        }

        $invoice->delete();
        session()->flash('success', $this->type === Invoice::TYPE_ESTIMATE ? __('Estimate deleted.') : __('Invoice deleted.'));
    }

    public function markSent(Invoice $invoice): void
    {
        abort_unless($invoice->type === $this->type, 404);
        $invoice->update([
            'status' => Invoice::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function duplicate(
        Invoice $invoice,
        InvoiceMakerContext $context,
        InvoiceNumberService $numberService,
    ): void {
        abort_unless($invoice->type === $this->type, 404);

        DB::transaction(function () use ($invoice, $context, $numberService): void {
            $copy = $invoice->replicate([
                'uuid',
                'invoice_number',
                'status',
                'amount_paid',
                'amount_due',
                'sent_at',
                'public_viewed_at',
                'accepted_at',
                'revision_requested_at',
            ]);
            $copy->invoice_number = $numberService->generate($context->profile(), $invoice->type);
            $copy->status = Invoice::STATUS_DRAFT;
            $copy->invoice_date = today();
            $copy->due_date = today()->addDays($context->profile()->payment_terms_days);
            $copy->amount_paid = 0;
            $copy->amount_due = $copy->grand_total;
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
        });

        session()->flash('success', __('Document duplicated.'));
    }

    public function convertToInvoice(
        Invoice $invoice,
        InvoiceMakerContext $context,
        InvoiceNumberService $numberService,
    ): void {
        abort_unless($invoice->isEstimate(), 404);
        $invoice->update([
            'type' => Invoice::TYPE_INVOICE,
            'invoice_number' => $numberService->generate($context->profile()),
            'status' => Invoice::STATUS_DRAFT,
        ]);
        session()->flash('success', __('Estimate converted to invoice.'));
        $this->redirectRoute('invoicemaker.invoices.show', $invoice, navigate: true);
    }

    public function render()
    {
        $documents = Invoice::query()
            ->with('client')
            ->where('type', $this->type)
            ->when($this->search, fn ($query) => $query->where(fn ($search) => $search
                ->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('client', fn ($client) => $client
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('company_name', 'like', "%{$this->search}%"))))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest('invoice_date')
            ->paginate(15);

        return view('invoicemaker::livewire.documents.index', compact('documents'));
    }
}
