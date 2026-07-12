<?php

namespace Modules\InvoiceMaker\Livewire\Documents;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Models\Invoice;
use Modules\InvoiceMaker\Models\Product;
use Modules\InvoiceMaker\Models\Template;
use Modules\InvoiceMaker\Services\InvoiceCalculationService;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Modules\InvoiceMaker\Services\InvoiceNumberService;

#[Layout('layouts.shell')]
class Form extends Component
{
    public ?Invoice $invoice = null;

    public string $type = Invoice::TYPE_INVOICE;

    public ?int $client_id = null;

    public ?int $template_id = null;

    public string $invoice_date = '';

    public string $due_date = '';

    public string $currency = 'EUR';

    public string $discount = '0';

    public ?string $notes = null;

    public ?string $payment_terms = null;

    public bool $is_recurring = false;

    public string $recurring_frequency = 'monthly';

    public array $items = [];

    public function mount(InvoiceMakerContext $context, ?Invoice $invoice = null): void
    {
        $this->invoice = $invoice;
        $this->type = request()->route('type', Invoice::TYPE_INVOICE);
        $profile = $context->profile();

        if ($invoice) {
            abort_unless($invoice->type === $this->type, 404);
            $this->fill($invoice->only([
                'client_id',
                'template_id',
                'currency',
                'discount',
                'notes',
                'payment_terms',
                'is_recurring',
                'recurring_frequency',
            ]));
            $this->invoice_date = $invoice->invoice_date->toDateString();
            $this->due_date = $invoice->due_date->toDateString();
            $this->items = $invoice->items->map->only([
                'product_id',
                'description',
                'quantity',
                'unit_price',
                'tax_rate',
                'discount',
            ])->all();
        } else {
            $this->invoice_date = today()->toDateString();
            $this->due_date = today()->addDays($profile->payment_terms_days)->toDateString();
            $this->currency = $profile->currency;
            $this->payment_terms = $profile->default_payment_terms ?? '';
            $this->template_id = Template::where('is_default', true)->value('id');
            $this->addItem();
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        return Product::orderBy('name')->get();
    }

    #[Computed]
    public function templates()
    {
        return Template::orderByDesc('is_default')->orderBy('name')->get();
    }

    #[Computed]
    public function totals(): array
    {
        return app(InvoiceCalculationService::class)->calculate($this->items, (float) $this->discount);
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id' => null,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'tax_rate' => 0,
            'discount' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(int $index): void
    {
        $productId = $this->items[$index]['product_id'] ?? null;
        $product = $productId ? Product::find($productId) : null;

        if (! $product) {
            return;
        }

        $this->items[$index] = [
            ...$this->items[$index],
            'description' => $product->name.($product->description ? " — {$product->description}" : ''),
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate,
        ];
    }

    public function save(
        InvoiceMakerContext $context,
        InvoiceNumberService $numberService,
        InvoiceCalculationService $calculationService,
    ): void {
        $data = $this->validate([
            'client_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'currency' => ['required', 'string', 'size:3'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['required_if:is_recurring,true', 'in:weekly,monthly,quarterly,yearly'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'between:0,100'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);
        Client::findOrFail($this->client_id);
        if ($this->template_id) {
            Template::findOrFail($this->template_id);
        }
        foreach (array_filter(array_column($this->items, 'product_id')) as $productId) {
            Product::findOrFail($productId);
        }

        $totals = $calculationService->calculate($data['items'], (float) $data['discount']);
        $profile = $context->profile();

        $document = DB::transaction(function () use ($data, $totals, $profile, $numberService): Invoice {
            $documentData = [
                ...collect($data)->except('items')->all(),
                ...collect($totals)->except('items')->all(),
                'type' => $this->type,
                'amount_due' => max(0, $totals['grand_total'] - (float) ($this->invoice?->amount_paid ?? 0)),
                'next_run_date' => $data['is_recurring'] ? $data['due_date'] : null,
            ];

            if ($this->invoice) {
                $this->invoice->update($documentData);
                $this->invoice->items()->delete();
                $document = $this->invoice;
            } else {
                $document = Invoice::create([
                    ...$documentData,
                    'invoice_number' => $numberService->generate($profile, $this->type),
                    'status' => Invoice::STATUS_DRAFT,
                ]);
            }

            foreach ($totals['items'] as $item) {
                $document->items()->create([
                    ...$item,
                    'team_id' => $document->team_id,
                ]);
            }

            return $document;
        });

        session()->flash('success', $this->invoice ? __('Document updated.') : __('Document created.'));
        $this->redirectRoute(
            $this->type === Invoice::TYPE_ESTIMATE
                ? 'invoicemaker.estimates.show'
                : 'invoicemaker.invoices.show',
            $document,
            navigate: true,
        );
    }

    public function render()
    {
        return view('invoicemaker::livewire.documents.form');
    }
}
