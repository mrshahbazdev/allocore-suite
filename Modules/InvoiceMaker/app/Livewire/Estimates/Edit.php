<?php

namespace Modules\InvoiceMaker\Livewire\Estimates;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\InvoiceCalculationService;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Edit extends Component
{
    public Invoice $estimate;

    public $client_id;

    public $template_id;

    public string $invoice_date;

    public string $due_date;

    public string $notes;

    public string $discount;

    public string $currency;

    public array $items = [];

    public string $product_search = '';

    protected InvoiceCalculationService $calculationService;

    protected array $rules = [
        'client_id' => 'required|exists:invoicemaker_clients,id',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'items.*.description' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:0',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.tax_rate' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'currency' => 'required|string|max:3',
    ];

    public function mount(Invoice $invoice): void
    {
        $this->estimate = $invoice;

        if ($invoice->type !== Invoice::TYPE_ESTIMATE) {
            abort(404);
        }

        $this->client_id = $invoice->client_id;
        $this->invoice_date = $invoice->invoice_date->toDateString();
        $this->due_date = $invoice->due_date->toDateString();
        $this->notes = $invoice->notes ?? '';
        $this->discount = $invoice->discount;
        $this->currency = $invoice->currency ?? app(InvoiceMakerContext::class)->profile()->currency;
        $this->template_id = $invoice->template_id;

        foreach ($invoice->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'discount' => $item->discount,
                'total' => $item->total,
            ];
        }
    }

    public function boot(InvoiceCalculationService $calculationService): void
    {
        $this->calculationService = $calculationService;
    }

    #[Computed]
    public function clients()
    {
        return app(InvoiceMakerContext::class)->profile()->clients()->orderBy('name')->get();
    }

    #[Computed]
    public function templates()
    {
        return app(InvoiceMakerContext::class)->profile()->templates;
    }

    #[Computed]
    public function products()
    {
        if (empty($this->product_search)) {
            return collect();
        }

        return app(InvoiceMakerContext::class)->profile()->products()
            ->where('name', 'like', '%'.$this->product_search.'%')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function totals()
    {
        return $this->calculationService->calculate($this->items, (float) $this->discount);
    }

    #[Computed]
    public function currency_symbol()
    {
        return match (strtoupper($this->currency)) {
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'PKR' => 'Rs',
            'CAD', 'AUD', 'USD' => '$',
            'AED' => 'د.إ',
            default => $this->currency.' ',
        };
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(Product $product): void
    {
        $this->items[] = [
            'product_id' => $product->id,
            'description' => $product->name,
            'quantity' => 1,
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => $product->price,
        ];
        $this->product_search = '';
    }

    public function updateItemTotal(int $index): void
    {
        $item = $this->items[$index];
        $total = $item['quantity'] * $item['unit_price'];
        $tax = $total * ($item['tax_rate'] / 100);
        $this->items[$index]['tax_amount'] = $tax;
        $this->items[$index]['total'] = $total + $tax - $item['discount'];
    }

    public function save(): void
    {
        $this->validate();

        $totals = $this->totals;

        $this->estimate->update([
            'client_id' => $this->client_id,
            'template_id' => $this->template_id,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'currency' => $this->currency,
            'discount' => $this->discount,
            'subtotal' => $totals['subtotal'],
            'tax_total' => $totals['tax_total'],
            'grand_total' => $totals['grand_total'],
            'amount_due' => $totals['grand_total'],
        ]);

        $this->estimate->items()->delete();
        foreach ($this->items as $item) {
            $this->estimate->items()->create([
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'discount' => $item['discount'],
                'total' => $item['total'],
            ]);
        }

        session()->flash('message', 'Estimate updated successfully.');
        $this->redirect(route('invoicemaker.estimates.index'), navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.estimates.edit');
    }
}
