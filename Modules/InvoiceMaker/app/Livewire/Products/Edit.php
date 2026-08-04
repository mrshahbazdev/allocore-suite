<?php

namespace Modules\InvoiceMaker\Livewire\Products;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Product;

#[Layout('layouts.shell')]
class Edit extends Component
{
    public Product $product;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public string $purchase_price = '';

    public string $unit = '';

    public string $tax_rate = '';

    public int $stock_quantity = 0;

    public bool $manage_stock = false;

    protected array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'purchase_price' => 'nullable|numeric|min:0',
        'unit' => 'required|string|max:50',
        'tax_rate' => 'required|numeric|min:0|max:100',
        'stock_quantity' => 'required_if:manage_stock,true|integer|min:0',
        'manage_stock' => 'boolean',
    ];

    public function mount(Product $product): void
    {
        $this->authorize('update', $product);
        $this->product = $product;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->price = $product->price;
        $this->purchase_price = $product->purchase_price ?? '0';
        $this->unit = $product->unit;
        $this->tax_rate = $product->tax_rate;
        $this->stock_quantity = $product->stock_quantity;
        $this->manage_stock = $product->manage_stock;
    }

    public function save(): void
    {
        $this->validate();

        $this->product->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'purchase_price' => $this->purchase_price ?: 0,
            'unit' => $this->unit,
            'tax_rate' => $this->tax_rate,
            'stock_quantity' => $this->manage_stock ? $this->stock_quantity : 0,
            'manage_stock' => $this->manage_stock,
        ]);

        session()->flash('message', 'Product updated successfully.');
        $this->redirect(route('invoicemaker.products.index'), navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.products.edit');
    }
}
