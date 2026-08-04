<?php

namespace Modules\InvoiceMaker\Livewire\Estimates;

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

    public string $search = '';

    public string $status = '';

    public string $sortBy = 'invoice_date';

    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function delete(int $id): void
    {
        $estimate = app(InvoiceMakerContext::class)->profile()->invoices()->where('type', Invoice::TYPE_ESTIMATE)->findOrFail($id);
        $estimate->delete();
        session()->flash('message', 'Estimate deleted successfully.');
    }

    public function convertToInvoice(int $id, InvoiceNumberService $invoiceNumberService): void
    {
        $business = app(InvoiceMakerContext::class)->profile();
        $estimate = $business->invoices()->where('type', Invoice::TYPE_ESTIMATE)->findOrFail($id);

        $estimate->update([
            'type' => Invoice::TYPE_INVOICE,
            'status' => Invoice::STATUS_DRAFT,
            'invoice_number' => $invoiceNumberService->generate($business, 'invoice'),
        ]);

        session()->flash('message', 'Estimate converted to Invoice successfully. New Invoice #: '.$estimate->invoice_number);
        $this->redirect(route('invoicemaker.invoices.show', $estimate), navigate: true);
    }

    public function render()
    {
        $query = app(InvoiceMakerContext::class)->profile()->invoices()->where('type', Invoice::TYPE_ESTIMATE)->with('client');

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

        $estimates = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        return view('invoicemaker::livewire.estimates.index', compact('estimates'));
    }
}
