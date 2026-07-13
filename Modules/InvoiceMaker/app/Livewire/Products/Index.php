<?php

namespace Modules\InvoiceMaker\Livewire\Products;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\Product;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Product $product): void
    {
        $product->delete();
        session()->flash('success', __('Product deleted.'));
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        return view('invoicemaker::livewire.products.index', compact('products'));
    }
}
