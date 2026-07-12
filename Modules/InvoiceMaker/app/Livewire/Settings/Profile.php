<?php

namespace Modules\InvoiceMaker\Livewire\Settings;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\InvoiceMaker\Models\Profile as ProfileModel;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;

#[Layout('layouts.shell')]
class Profile extends Component
{
    public ProfileModel $profile;

    public string $name = '';

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $tax_number = null;

    public string $currency = 'EUR';

    public string $timezone = 'UTC';

    public ?string $bank_details = null;

    public ?string $iban = null;

    public ?string $bic = null;

    public string $invoice_number_prefix = 'INV';

    public string $estimate_number_prefix = 'EST';

    public int $payment_terms_days = 14;

    public ?string $default_payment_terms = null;

    public string $late_fee_percentage = '0';

    public bool $enable_automated_reminders = false;

    public int $reminder_days_interval = 7;

    public ?string $stripe_public_key = null;

    public string $stripe_secret_key = '';

    public function mount(InvoiceMakerContext $context): void
    {
        $this->profile = $context->profile();
        $this->fill($this->profile->only([
            'name',
            'email',
            'phone',
            'address',
            'tax_number',
            'currency',
            'timezone',
            'bank_details',
            'iban',
            'bic',
            'invoice_number_prefix',
            'estimate_number_prefix',
            'payment_terms_days',
            'default_payment_terms',
            'late_fee_percentage',
            'enable_automated_reminders',
            'reminder_days_interval',
            'stripe_public_key',
        ]));
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'timezone'],
            'bank_details' => ['nullable', 'string'],
            'iban' => ['nullable', 'string', 'max:255'],
            'bic' => ['nullable', 'string', 'max:255'],
            'invoice_number_prefix' => ['required', 'alpha_dash', 'max:20'],
            'estimate_number_prefix' => ['required', 'alpha_dash', 'max:20'],
            'payment_terms_days' => ['required', 'integer', 'between:0,365'],
            'default_payment_terms' => ['nullable', 'string'],
            'late_fee_percentage' => ['required', 'numeric', 'between:0,100'],
            'enable_automated_reminders' => ['boolean'],
            'reminder_days_interval' => ['required', 'integer', 'between:1,365'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['stripe_secret_key'] === '') {
            unset($data['stripe_secret_key']);
        }

        $this->profile->update($data);
        $this->reset('stripe_secret_key');
        session()->flash('success', __('Invoice settings updated.'));
    }

    public function render()
    {
        return view('invoicemaker::livewire.settings.profile');
    }
}
