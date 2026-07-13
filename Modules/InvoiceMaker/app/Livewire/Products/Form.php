<?php

namespace Modules\InvoiceMaker\Livewire\Products;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Product;

#[Layout('layouts.shell')]
class Form extends Component
{
    public ?Product $product = null;

    public string $name = '';

    public ?string $description = null;

    public string $price = '0';

    public string $purchase_price = '0';

    public string $unit = 'unit';

    public string $tax_rate = '0';

    public bool $manage_stock = false;

    public string $stock_quantity = '0';

    public function mount(?Product $product = null): void
    {
        $this->product = $product;

        if ($product) {
            $this->fill($product->only([
                'name',
                'description',
                'price',
                'purchase_price',
                'unit',
                'tax_rate',
                'manage_stock',
                'stock_quantity',
            ]));
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'tax_rate' => ['required', 'numeric', 'between:0,100'],
            'manage_stock' => ['boolean'],
            'stock_quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $this->product?->update($data) ?? Product::create($data);

        session()->flash('success', $this->product ? __('Product updated.') : __('Product created.'));
        $this->redirectRoute('invoicemaker.products.index', navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.products.form');
    }
}
