<?php

namespace Modules\InvoiceMaker\Livewire\Invoices;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Services\AiService;
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

    public string $payment_terms = '';

    public string $discount = '0';

    public string $currency = 'USD';

    public $quick = false;

    public bool $is_recurring = false;

    public string $recurring_frequency = 'monthly';

    public string $next_run_date = '';

    public array $items = [];

    public string $product_search = '';

    public bool $requiresDuplicateConfirmation = false;

    public bool $forceCreate = false;

    public ?int $potentialDuplicateId = null;

    public ?string $potentialDuplicateNumber = null;

    public ?string $potentialDuplicateDate = null;

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
        'discount' => 'nullable|numeric|min:0',
        'currency' => 'required|string|max:3',
        'is_recurring' => 'boolean',
        'recurring_frequency' => 'required_if:is_recurring,true|nullable|string',
        'next_run_date' => 'required_if:is_recurring,true|nullable|date',
    ];

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->due_date = now()->addDays(30)->toDateString();
        $this->next_run_date = now()->toDateString();
        $this->template_id = app(InvoiceMakerContext::class)->profile()->templates()->where('is_default', true)->first()?->id;
        $this->payment_terms = app(InvoiceMakerContext::class)->profile()->payment_terms ?? app(InvoiceMakerContext::class)->profile()->templates()->where('is_default', true)->first()?->payment_terms ?? '';
        $this->currency = app(InvoiceMakerContext::class)->profile()->currency;
        $this->quick = request('quick', false) ? true : false;

        if ($this->quick) {
            $this->addItem(); // auto-add a first item for quick mode
        }
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
            'stock_quantity' => $product->stock_quantity,
            'manage_stock' => $product->manage_stock,
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
            $this->validate([
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
            ]);
        } elseif ($this->step === 3) {
            $this->validate([
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
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

        if (! $this->forceCreate) {
            $duplicate = $this->findPotentialDuplicate();

            if ($duplicate) {
                $this->requiresDuplicateConfirmation = true;
                $this->potentialDuplicateId = $duplicate->id;
                $this->potentialDuplicateNumber = $duplicate->invoice_number;
                $this->potentialDuplicateDate = $duplicate->created_at->format('M d, Y H:i');

                return;
            }
        }

        $this->reset(['requiresDuplicateConfirmation', 'potentialDuplicateId', 'potentialDuplicateNumber', 'potentialDuplicateDate']);

        $totals = $this->totals;

        $invoice = Invoice::create([
            'team_id' => app(InvoiceMakerContext::class)->profile()->team_id,
            'client_id' => $this->client_id,
            'template_id' => $this->template_id,
            'invoice_number' => $this->invoiceNumberService->generate(app(InvoiceMakerContext::class)->profile()),
            'status' => Invoice::STATUS_DRAFT,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'payment_terms' => $this->payment_terms,
            'currency' => $this->currency,
            'subtotal' => $totals['subtotal'],
            'tax_total' => $totals['tax_total'],
            'discount' => $totals['discount'],
            'grand_total' => $totals['grand_total'],
            'amount_paid' => 0,
            'amount_due' => $totals['grand_total'],
            'is_recurring' => $this->is_recurring,
            'recurring_frequency' => $this->is_recurring ? $this->recurring_frequency : null,
            'next_run_date' => $this->is_recurring ? $this->next_run_date : null,
        ]);

        foreach ($this->items as $item) {
            $invoice->items()->create([
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

        $this->forceCreate = false;
        session()->flash('message', 'Invoice created successfully.');
        $this->redirect(route('invoicemaker.invoices.show', $invoice), navigate: true);
    }

    public function confirmDuplicateCreate(): void
    {
        $this->forceCreate = true;
        $this->save();
    }

    public function openExistingInvoice(): void
    {
        $this->reset(['requiresDuplicateConfirmation', 'potentialDuplicateId', 'potentialDuplicateNumber', 'potentialDuplicateDate', 'forceCreate']);
        $this->redirect(route('invoicemaker.invoices.show', $this->potentialDuplicateId), navigate: true);
    }

    protected function findPotentialDuplicate(): ?Invoice
    {
        $currentItems = collect($this->items)
            ->map(fn ($item) => trim(strtolower($item['description'])))
            ->filter()
            ->sort()
            ->values();

        if ($currentItems->isEmpty()) {
            return null;
        }

        $grandTotal = $this->totals['grand_total'];

        return app(InvoiceMakerContext::class)->profile()->invoices()
            ->where('type', Invoice::TYPE_INVOICE)
            ->where('client_id', $this->client_id)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->where('created_at', '>=', now()->subDay())
            ->where('grand_total', $grandTotal)
            ->with('items')
            ->get()
            ->first(function ($invoice) use ($currentItems) {
                $invoiceItems = $invoice->items->map(fn ($item) => trim(strtolower($item->description)))
                    ->filter()
                    ->sort()
                    ->values();

                return $currentItems->count() === $invoiceItems->count()
                && $currentItems->diff($invoiceItems)->isEmpty();
            });
    }

    public function render()
    {
        return view('invoicemaker::livewire.invoices.create');
    }
}
