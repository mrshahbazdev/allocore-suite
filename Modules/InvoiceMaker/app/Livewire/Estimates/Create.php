<?php

namespace Modules\InvoiceMaker\Livewire\Estimates;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\InvoiceCalculationService;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Modules\InvoiceMaker\Services\InvoiceNumberService;

#[Layout('layouts.shell')]
class Create extends Component
{
    public int $step = 1;

    public $client_id = null;

    public $template_id = null;

    public string $invoice_date = '';

    public string $due_date = '';

    public string $notes = '';

    public string $discount = '0';

    public string $currency = 'USD';

    public $quick = false;

    public array $items = [];

    public string $product_search = '';

    protected InvoiceNumberService $invoiceNumberService;

    protected InvoiceCalculationService $calculationService;

    protected array $rules = [
        'client_id' => 'required|exists:clients,id',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'items.*.description' => 'required|string',
        'items.*.quantity' => 'required|numeric|min:0',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.tax_rate' => 'required|numeric|min:0',
        'template_id' => 'required|exists:templates,id',
        'discount' => 'nullable|numeric|min:0',
        'currency' => 'required|string|max:3',
    ];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->due_date = now()->addDays(14)->toDateString(); // Estimates usually have shorter expiration
        $this->template_id = app(InvoiceMakerContext::class)->profile()->templates()->where('is_default', true)->first()?->id;
        $this->currency = app(InvoiceMakerContext::class)->profile()->currency;
    }

    public function boot(InvoiceNumberService $invoiceNumberService, InvoiceCalculationService $calculationService): void
    {
        $this->invoiceNumberService = $invoiceNumberService;
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
        $newItem = [
            'product_id' => $product->id,
            'description' => $product->name.' - '.$product->description,
            'quantity' => 1,
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate,
            'tax_amount' => 0,
            'discount' => 0,
            'total' => 0,
        ];

        $this->items[] = $newItem;
        $newIndex = count($this->items) - 1;
        $this->updateItemTotal($newIndex);

        $this->product_search = '';
    }

    public function updatedClientId($value): void
    {
        if ($value) {
            $client = Client::find($value);
            if ($client && $client->currency) {
                $this->currency = $client->currency;
            }
        }
    }

    public function updateItemTotal(int $index): void
    {
        $item = $this->items[$index];
        $total = $item['quantity'] * $item['unit_price'];
        $tax = $total * ($item['tax_rate'] / 100);
        $this->items[$index]['tax_amount'] = $tax;
        $this->items[$index]['total'] = $total + $tax - $item['discount'];
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate(['client_id' => 'required|exists:clients,id']);
        } elseif ($this->step === 2) {
            if (count($this->items) === 0) {
                $this->addError('items', 'Please add at least one item.');

                return;
            }
            $this->validate([
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);
        } elseif ($this->step === 3) {
            $this->validate([
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'template_id' => 'required|exists:templates,id',
            ]);
        }

        if ($this->step < 4) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function save(): void
    {
        try {
            $this->validate();

            $totals = $this->totals;

            $estimate = Invoice::create([
                'team_id' => app(InvoiceMakerContext::class)->profile()->team_id,
                'client_id' => $this->client_id,
                'template_id' => $this->template_id,
                'invoice_number' => $this->invoiceNumberService->generate(app(InvoiceMakerContext::class)->profile(), 'estimate'),
                'status' => Invoice::STATUS_DRAFT,
                'type' => Invoice::TYPE_ESTIMATE,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'notes' => $this->notes,
                'currency' => $this->currency,
                'subtotal' => $totals['subtotal'],
                'tax_total' => $totals['tax_total'],
                'discount' => $totals['discount'],
                'grand_total' => $totals['grand_total'],
                'amount_paid' => 0,
                'amount_due' => $totals['grand_total'],
            ]);

            foreach ($this->items as $item) {
                $estimate->items()->create([
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

            session()->flash('message', 'Estimate created successfully.');
            $this->redirect(route('invoicemaker.estimates.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating estimate: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('invoicemaker::livewire.estimates.create');
    }
}
