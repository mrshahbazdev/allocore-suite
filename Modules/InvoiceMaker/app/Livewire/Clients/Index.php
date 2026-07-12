<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\InvoiceMaker\Models\Client;

#[Layout('layouts.shell')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Client $client): void
    {
        if ($client->invoices()->exists()) {
            session()->flash('warning', __('Clients with invoices cannot be deleted.'));

            return;
        }

        $client->delete();
        session()->flash('success', __('Client deleted.'));
    }

    public function render()
    {
        $clients = Client::query()
            ->withCount('invoices')
            ->when($this->search, fn ($query) => $query->where(fn ($search) => $search
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('company_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        return view('invoicemaker::livewire.clients.index', compact('clients'));
    }
}
