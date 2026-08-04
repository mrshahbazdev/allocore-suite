<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Edit extends Component
{
    public Client $client;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company_name = '';

    public string $address = '';

    public string $tax_number = '';

    public string $notes = '';

    public string $currency = 'USD';

    public string $language = 'en';

    public string $email_subject = '';

    public string $email_template = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:255',
        'company_name' => 'nullable|string|max:255',
        'address' => 'nullable|string',
        'tax_number' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
        'currency' => 'nullable|string|max:3',
        'language' => 'required|string|in:en,es,fr,de',
        'email_subject' => 'nullable|string|max:255',
        'email_template' => 'nullable|string',
    ];

    public function mount(Client $client): void
    {
        $this->authorize('update', $client);
        $this->client = $client;
        $this->name = $client->name;
        $this->email = $client->email ?? '';
        $this->phone = $client->phone ?? '';
        $this->company_name = $client->company_name ?? '';
        $this->address = $client->address ?? '';
        $this->tax_number = $client->tax_number ?? '';
        $this->notes = $client->notes ?? '';
        $this->currency = $client->currency ?? app(InvoiceMakerContext::class)->profile()->currency;
        $this->language = $client->language ?? 'en';
        $this->email_subject = $client->email_subject ?? '';
        $this->email_template = $client->email_template ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $this->client->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'tax_number' => $this->tax_number,
            'notes' => $this->notes,
            'currency' => $this->currency,
            'language' => $this->language,
            'email_subject' => $this->email_subject,
            'email_template' => $this->email_template,
        ]);

        session()->flash('message', 'Client updated successfully.');
        $this->redirect(route('invoicemaker.clients.index'), navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.clients.edit');
    }
}
