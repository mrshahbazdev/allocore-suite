<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Client;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Form extends Component
{
    public ?Client $client = null;

    public string $name = '';

    public ?string $company_name = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $tax_number = null;

    public string $currency = 'EUR';

    public string $language = 'en';

    public ?string $notes = null;

    public function mount(InvoiceMakerContext $context, ?Client $client = null): void
    {
        $this->client = $client;

        if ($client) {
            $this->fill($client->only([
                'name',
                'company_name',
                'email',
                'phone',
                'address',
                'tax_number',
                'currency',
                'language',
                'notes',
            ]));
            $this->currency = $client->currency ?? $context->profile()->currency;
        } else {
            $this->currency = $context->profile()->currency;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'language' => ['required', 'string', 'max:5'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->client?->update($data) ?? Client::create($data);

        session()->flash('success', $this->client ? __('Client updated.') : __('Client created.'));
        $this->redirectRoute('invoicemaker.clients.index', navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.clients.form');
    }
}
