<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;

#[Layout('layouts.shell')]
class Show extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function render()
    {
        return view('invoicemaker::livewire.clients.show', [
            'invoices' => $this->client->invoices()->orderBy('created_at', 'desc')->get(),
        ]);
    }
}
