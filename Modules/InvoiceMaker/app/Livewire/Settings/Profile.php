<?php

namespace Modules\InvoiceMaker\Livewire\Settings;

use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\InvoiceMaker\Models\Profile as ProfileModel;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Modules\InvoiceMaker\Services\MailConfigurationService;
use Stripe\StripeClient;

#[Layout('layouts.shell')]
class Profile extends Component
{
    use WithFileUploads;

    public ?ProfileModel $business = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $currency = 'USD';

    public string $timezone = 'UTC';

    public string $tax_number = '';

    public string $bank_details = '';

    public string $iban = '';

    public string $bic = '';

    public string $payment_terms = '';

    public string $language = 'en';

    public string $invoice_number_prefix = 'INV';

    public int $invoice_number_next = 1;

    public string $estimate_number_prefix = 'EST';

    public int $estimate_number_next = 1;

    public string $booking_number_prefix = 'EXP';

    public int $booking_number_next = 1;

    public string $bank_booking_account = '1000';

    public string $cash_booking_account = '1200';

    public string $smtp_host = '';

    public ?int $smtp_port = 587;

    public string $smtp_username = '';

    public string $smtp_password = '';

    public string $smtp_encryption = 'tls';

    public string $smtp_from_address = '';

    public string $smtp_from_name = '';

    public bool $smtp_verify_ssl = true;

    public $logo;

    public bool $stripe_onboarding_complete = false;

    public bool $enable_automated_reminders = true;

    public int $reminder_days_interval = 7;

    public bool $accept_network_invoices = false;

    public float $late_fee_percentage = 0;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'currency' => 'required|string|max:3',
            'timezone' => 'required|string',
            'tax_number' => 'nullable|string|max:255',
            'bank_details' => 'nullable|string',
            'iban' => 'nullable|string|max:34',
            'bic' => 'nullable|string|max:11',
            'payment_terms' => 'nullable|string',
            'language' => 'required|string|in:en,de,es,fr,it,pt,ar,zh,ja,ru',
            'invoice_number_prefix' => 'required|string|max:10',
            'invoice_number_next' => 'required|integer|min:1',
            'estimate_number_prefix' => 'required|string|max:10',
            'estimate_number_next' => 'required|integer|min:1',
            'booking_number_prefix' => 'nullable|string|max:10',
            'booking_number_next' => 'required|integer|min:1',
            'bank_booking_account' => 'nullable|string|max:20',
            'cash_booking_account' => 'nullable|string|max:20',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|max:10',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'smtp_verify_ssl' => 'boolean',
            'logo' => 'nullable|image|max:2048',
            'enable_automated_reminders' => 'boolean',
            'reminder_days_interval' => 'required_if:enable_automated_reminders,true|integer|min:1|max:365',
            'accept_network_invoices' => 'boolean',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function mount(): void
    {
        $this->business = app(InvoiceMakerContext::class)->profile();

        if ($this->business) {
            $this->name = $this->business->name ?? Auth::user()?->name ?? '';
            $this->email = $this->business->email ?? Auth::user()?->email ?? '';
            $this->phone = $this->business->phone ?? '';
            $this->address = $this->business->address ?? '';
            $this->currency = $this->business->currency ?? 'EUR';
            $this->timezone = $this->business->timezone ?? 'Europe/Berlin';
            $this->tax_number = $this->business->tax_number ?? '';
            $this->bank_details = $this->business->bank_details ?? '';
            $this->iban = $this->business->iban ?? '';
            $this->bic = $this->business->bic ?? '';
            $this->payment_terms = $this->business->payment_terms ?? '';
            $this->invoice_number_prefix = $this->business->invoice_number_prefix ?? 'INV';
            $this->invoice_number_next = $this->business->invoice_number_next ?? 1;
            $this->estimate_number_prefix = $this->business->estimate_number_prefix ?? 'EST';
            $this->estimate_number_next = $this->business->estimate_number_next ?? 1;
            $this->booking_number_prefix = $this->business->booking_number_prefix ?? 'EXP';
            $this->booking_number_next = $this->business->booking_number_next ?? 1;
            $this->bank_booking_account = $this->business->bank_booking_account ?? '1000';
            $this->cash_booking_account = $this->business->cash_booking_account ?? '1200';
            $this->smtp_host = $this->business->smtp_host ?? '';
            $this->smtp_port = $this->business->smtp_port ?? 587;
            $this->smtp_username = $this->business->smtp_username ?? '';
            $this->smtp_password = $this->business->smtp_password ?? '';
            $this->smtp_encryption = $this->business->smtp_encryption ?? 'tls';
            $this->smtp_from_address = $this->business->smtp_from_address ?? '';
            $this->smtp_from_name = $this->business->smtp_from_name ?? '';
            $this->smtp_verify_ssl = $this->business->smtp_verify_ssl ?? true;
            $this->stripe_onboarding_complete = $this->business->stripe_onboarding_complete ?? false;
            $this->enable_automated_reminders = $this->business->enable_automated_reminders ?? true;
            $this->reminder_days_interval = $this->business->reminder_days_interval ?? 7;
            $this->accept_network_invoices = $this->business->accept_network_invoices ?? false;
            $this->late_fee_percentage = $this->business->late_fee_percentage ?? 0;
        } else {
            // Defaults for new business
            $this->name = Auth::user()?->name ?? '';
            $this->email = Auth::user()?->email ?? '';
            $this->currency = 'EUR';
            $this->timezone = 'Europe/Berlin';
        }

        $this->language = Auth::user()->locale ?? 'en';
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'currency' => $this->currency,
            'timezone' => $this->timezone,
            'tax_number' => $this->tax_number,
            'bank_details' => $this->bank_details,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'payment_terms' => $this->payment_terms,
            'invoice_number_prefix' => $this->invoice_number_prefix,
            'invoice_number_next' => $this->invoice_number_next,
            'estimate_number_prefix' => $this->estimate_number_prefix,
            'estimate_number_next' => $this->estimate_number_next,
            'booking_number_prefix' => $this->booking_number_prefix,
            'booking_number_next' => $this->booking_number_next,
            'bank_booking_account' => $this->bank_booking_account,
            'cash_booking_account' => $this->cash_booking_account,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'smtp_password' => $this->smtp_password,
            'smtp_encryption' => $this->smtp_encryption,
            'smtp_from_address' => $this->smtp_from_address,
            'smtp_from_name' => $this->smtp_from_name,
            'smtp_verify_ssl' => $this->smtp_verify_ssl,
            'enable_automated_reminders' => $this->enable_automated_reminders,
            'reminder_days_interval' => $this->reminder_days_interval,
            'accept_network_invoices' => $this->accept_network_invoices,
            'late_fee_percentage' => $this->late_fee_percentage,
        ];

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $data['logo'] = $path;
        }

        if ($this->business) {
            $this->business->update($data);
        } else {
            // Create new business and link to user
            $data['user_id'] = Auth::id();
            $this->business = ProfileModel::create($data);
            Auth::user()->update(['current_team_id' => $this->business->team_id]);
        }

        Auth::user()->update(['locale' => $this->language]);
        App::setLocale($this->language);

        session()->flash('message', 'Profile profile and language updated successfully.');
    }

    public function removeLogo(): void
    {
        if ($this->business) {
            $this->business->update(['logo' => null]);
            $this->logo = null;
            session()->flash('message', 'Logo removed successfully.');
        }
    }

    public function connectStripe()
    {
        if (! $this->business) {
            return;
        }

        $stripeSecret = env('STRIPE_SECRET');

        if (! $stripeSecret) {
            session()->flash('error', 'Stripe secret key is not configured in .env file.');

            return;
        }

        $stripe = new StripeClient($stripeSecret);

        // Create Stripe account if it doesn't exist
        if (! $this->business->stripe_account_id) {
            $account = $stripe->accounts->create([
                'type' => 'standard',
                'email' => $this->business->user->email,
            ]);

            $this->business->update([
                'stripe_account_id' => $account->id,
            ]);
        }

        // Generate onboarding link
        $accountLink = $stripe->accountLinks->create([
            'account' => $this->business->stripe_account_id,
            'refresh_url' => route('invoicemaker.settings.profile'),
            'return_url' => route('invoicemaker.settings.profile'),
            'type' => 'account_onboarding',
        ]);

        return redirect()->away($accountLink->url);
    }

    public function testSmtpConnection(): void
    {
        $this->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'smtp_from_address' => 'required|email',
        ]);

        try {
            // For testing, we send a simple raw email to the authenticated user
            $userEmail = Auth::user()->email;

            $testBusiness = $this->business ?? new ProfileModel;
            $testBusiness->forceFill([
                'smtp_host' => $this->smtp_host,
                'smtp_port' => $this->smtp_port,
                'smtp_username' => $this->smtp_username,
                'smtp_password' => $this->smtp_password,
                'smtp_encryption' => $this->smtp_encryption,
                'smtp_verify_ssl' => $this->smtp_verify_ssl,
            ]);

            MailConfigurationService::getMailer($testBusiness)->raw('This is a test email to verify your SMTP settings for '.config('app.name'), function ($message) use ($userEmail) {
                $message->to($userEmail)
                    ->subject('SMTP Connection Test')
                    ->from($this->smtp_from_address, $this->smtp_from_name ?: config('mail.from.name'));
            });

            session()->flash('message', 'SMTP test email sent successfully to '.$userEmail);
        } catch (Exception $e) {
            session()->flash('error', 'SMTP Connection failed: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('invoicemaker::livewire.settings.profile');
    }
}
