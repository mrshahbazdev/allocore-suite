<?php

namespace Modules\InvoiceMaker\Livewire\Clients;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company_name = '';

    public string $address = '';

    public string $tax_number = '';

    public string $notes = '';

    public string $currency = '';

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

    public function mount(): void
    {
        $this->currency = app(InvoiceMakerContext::class)->profile()->currency;
    }

    public function save(): void
    {
        $this->validate();

        app(InvoiceMakerContext::class)->profile()->clients()->create([
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

        session()->flash('message', 'Client created successfully.');
        $this->redirect(route('invoicemaker.clients.index'), navigate: true);
    }

    public function render()
    {
        return view('invoicemaker::livewire.clients.create');
    }
}
