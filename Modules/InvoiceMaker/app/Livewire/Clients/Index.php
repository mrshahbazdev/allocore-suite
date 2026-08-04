<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $client = app(InvoiceMakerContext::class)->profile()->clients()->findOrFail($id);
        $client->delete();
        session()->flash('message', 'Client deleted successfully.');
    }

    public function render()
    {
        $clients = app(InvoiceMakerContext::class)->profile()->clients()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('company_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('invoicemaker::livewire.clients.index', compact('clients'));
    }
}
