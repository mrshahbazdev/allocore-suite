<?php

namespace Modules\InvoiceMaker\Livewire\Invoices;

use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\AiService;
use Modules\InvoiceMaker\Services\InvoiceCalculationService;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Edit extends Component
{
    public Invoice $invoice;

    public $client_id = null;

    public string $invoice_date = '';

    public string $due_date = '';

    public string $notes = '';

    public string $payment_terms = '';

    public string $discount = '0';

    public string $currency = 'USD';

    public bool $is_recurring = false;

    public string $recurring_frequency = 'monthly';

    public string $next_run_date = '';

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
        'is_recurring' => 'boolean',
        'recurring_frequency' => 'required_if:is_recurring,true|nullable|string',
        'next_run_date' => 'required_if:is_recurring,true|nullable|date',
    ];

    public function mount(Invoice $invoice): void
    {
        $this->authorize('update', $invoice);
        $this->invoice = $invoice;

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            session()->flash('error', 'Only draft invoices can be edited.');
            $this->redirect(route('invoicemaker.invoices.show', $invoice), navigate: true);

            return;
        }

        $this->client_id = $invoice->client_id;
        $this->invoice_date = $invoice->invoice_date instanceof Carbon ? $invoice->invoice_date->toDateString() : $invoice->invoice_date;
        $this->due_date = $invoice->due_date instanceof Carbon ? $invoice->due_date->toDateString() : $invoice->due_date;
        $this->notes = $invoice->notes ?? '';
        $this->payment_terms = $invoice->payment_terms ?? '';
        $this->discount = $invoice->discount;
        $this->currency = $invoice->currency ?? app(InvoiceMakerContext::class)->profile()->currency;
        $this->is_recurring = (bool) $invoice->is_recurring;
        $this->recurring_frequency = $invoice->recurring_frequency ?? 'monthly';
        $this->next_run_date = $invoice->next_run_date instanceof Carbon ? $invoice->next_run_date->toDateString() : ($invoice->next_run_date ?? '');

        foreach ($invoice->items as $item) {
            $this->items[] = [
                'product_id' => $item->product_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'tax_rate' => $item->tax_rate,
                'tax_amount' => $item->tax_amount,
                'discount' => $item->discount,
                'total' => $item->total,
                'stock_quantity' => $item->product->stock_quantity ?? 0,
                'manage_stock' => $item->product->manage_stock ?? false,
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
        // Add the product data to the item array
        $newItem = [
            'product_id' => $product->id,
            'description' => $product->name.' - '.$product->description,
            'quantity' => 1,
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
            'stock_quantity' => $product->stock_quantity,
            'manage_stock' => $product->manage_stock,
        ];

        $this->items[] = $newItem;
        $newIndex = count($this->items) - 1;

        // Compute total now
        $this->updateItemTotal($newIndex);

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

    public function generateNotesWithAi(AiService $aiService): void
    {
        if (empty($this->items) || count(array_filter(array_column($this->items, 'description'))) === 0) {
            session()->flash('error', __('Please add some items with descriptions first before generating notes.'));

            return;
        }

        $promptBase = 'Generate a professional, polite, and concise description for this invoice based on the following items:';

        $itemsList = collect($this->items)
            ->filter(fn ($item) => ! empty($item['description']))
            ->map(fn ($item) => "- {$item['quantity']}x {$item['description']}")
            ->implode("\n");

        $prompt = $promptBase."\n\n"."Items:\n".$itemsList;

        try {
            $this->notes = trim($aiService->generateText($prompt));
        } catch (\Exception $e) {
            session()->flash('error', __('AI Generation failed').': '.$e->getMessage());
        }
    }

    public function save(): void
    {
        $this->validate();

        $totals = $this->totals;

        $this->invoice->update([
            'client_id' => $this->client_id,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
            'currency' => $this->currency,
            'subtotal' => $totals['subtotal'],
            'tax_total' => $totals['tax_total'],
            'discount' => $totals['discount'],
            'grand_total' => $totals['grand_total'],
            'amount_due' => $totals['grand_total'],
            'is_recurring' => $this->is_recurring,
            'recurring_frequency' => $this->is_recurring ? $this->recurring_frequency : null,
            'next_run_date' => $this->is_recurring ? $this->next_run_date : null,
        ]);

        $this->invoice->items()->delete();

        foreach ($this->items as $item) {
            $this->invoice->items()->create([
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

        session()->flash('message', 'Invoice updated successfully.');
        $this->redirect(route('invoicemaker.invoices.show', $this->invoice), navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.invoices.edit');
    }
}
