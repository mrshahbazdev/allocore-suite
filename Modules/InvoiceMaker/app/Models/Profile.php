<?php

namespace Modules\InvoiceMaker\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Profile extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_profiles';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'logo',
        'currency',
        'timezone',
        'tax_number',
        'bank_details',
        'last_reminder_sent_at',
        'inventory_deducted',
        'payment_terms',
        'stripe_account_id',
        'stripe_onboarding_complete',
        'plan',
        'invoice_number_prefix',
        'invoice_number_next',
        'estimate_number_prefix',
        'estimate_number_next',
        'booking_number_prefix',
        'booking_number_next',
        'bank_booking_account',
        'cash_booking_account',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
        'iban',
        'bic',
        'theme_id',
        'page_bg_color_id',
        'card_bg_color_id',
        'text_color_id',
        'stripe_public_key',
        'stripe_secret_key',
        'enable_automated_reminders',
        'reminder_days_interval',
        'accept_network_invoices',
        'late_fee_percentage',
        'smtp_verify_ssl',
    ];

    protected $casts = [
        'enable_automated_reminders' => 'boolean',
        'accept_network_invoices' => 'boolean',
        'late_fee_percentage' => 'float',
        'smtp_verify_ssl' => 'boolean',
    ];

    public function seedDefaultTemplates()
    {
        $templates = [
            [
                'name' => 'Minimalist Professional',
                'primary_color' => '#1f2937',
                'font_family' => 'Helvetica, sans-serif',
                'header_style' => 'simple',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => true,
                'payment_terms' => 'Please pay within 15 days.',
                'footer_message' => 'Thank you for your business!',
                'enable_qr' => true,
            ],
            [
                'name' => 'Creative Studio Orange',
                'primary_color' => '#f97316',
                'font_family' => 'Arial, sans-serif',
                'header_style' => 'bold',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Net 30 days. Late fees apply.',
                'footer_message' => 'We appreciate your creative collaboration.',
                'enable_qr' => true,
            ],
            [
                'name' => 'Tech Startup Blue',
                'primary_color' => '#3b82f6',
                'font_family' => 'Courier New, monospace',
                'header_style' => 'center',
                'show_tax' => true,
                'show_discount' => false,
                'is_default' => false,
                'payment_terms' => 'Payable upon receipt.',
                'footer_message' => 'Built with code & coffee.',
                'enable_qr' => true,
            ],
            [
                'name' => 'Elegant Emerald',
                'primary_color' => '#10b981',
                'font_family' => 'Georgia, serif',
                'header_style' => 'simple',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Due in 7 days.',
                'footer_message' => 'Partnering for your success.',
                'enable_qr' => false,
            ],
            [
                'name' => 'Corporate Navy',
                'primary_color' => '#1e3a8a',
                'font_family' => 'Times New Roman, serif',
                'header_style' => 'bold',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Standard Net 30 terms.',
                'footer_message' => 'Reliable & Professional Service.',
                'enable_qr' => true,
            ],
            [
                'name' => 'Modern Purple',
                'primary_color' => '#8b5cf6',
                'font_family' => 'Trebuchet MS, sans-serif',
                'header_style' => 'center',
                'show_tax' => false,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Payment due before starting project phase 2.',
                'footer_message' => 'Designing the future.',
                'enable_qr' => true,
            ],
            [
                'name' => 'Sunset Red',
                'primary_color' => '#ef4444',
                'font_family' => 'Verdana, sans-serif',
                'header_style' => 'simple',
                'show_tax' => true,
                'show_discount' => false,
                'is_default' => false,
                'payment_terms' => 'Please initiate wire transfer immediately.',
                'footer_message' => 'Fast and secure.',
                'enable_qr' => false,
            ],
            [
                'name' => 'Boutique Rose',
                'primary_color' => '#f43f5e',
                'font_family' => 'Palatino, serif',
                'header_style' => 'bold',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Payable via credit card link below.',
                'footer_message' => 'Thank you for shopping local!',
                'enable_qr' => true,
            ],
            [
                'name' => 'Midnight Black',
                'primary_color' => '#000000',
                'font_family' => 'Impact, sans-serif',
                'header_style' => 'center',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Strictly 14 days.',
                'footer_message' => 'Premium Quality Delivered.',
                'enable_qr' => true,
            ],
            [
                'name' => 'Eco Green',
                'primary_color' => '#65a30d',
                'font_family' => 'Arial, Helvetica, sans-serif',
                'header_style' => 'simple',
                'show_tax' => true,
                'show_discount' => true,
                'is_default' => false,
                'payment_terms' => 'Pay to our eco-friendly digital wallet.',
                'footer_message' => 'Save paper. Keep this digital!',
                'enable_qr' => true,
            ],
        ];

        foreach ($templates as $tmpl) {
            $this->templates()->updateOrCreate(
                ['name' => $tmpl['name']],
                $tmpl
            );
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'team_id', 'team_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'team_id', 'team_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'team_id', 'team_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'team_id', 'team_id')->where('type', Invoice::TYPE_INVOICE);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Invoice::class, 'team_id', 'team_id')->where('type', Invoice::TYPE_ESTIMATE);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'team_id', 'team_id');
    }

    public function getCurrencySymbolAttribute()
    {
        return match (strtoupper($this->currency)) {
            'USD', 'CAD', 'AUD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'PKR', 'LKR', 'NPR' => 'Rs',
            'AED' => 'د.إ',
            default => '$',
        };
    }

    public function hasCustomSmtp(): bool
    {
        return ! empty($this->smtp_host) &&
            ! empty($this->smtp_port) &&
            ! empty($this->smtp_username) &&
            ! empty($this->smtp_password);
    }

    /**
     * Generate an EPC-compliant QR code string for SEPA Credit Transfers.
     * Standard: EPC069-12
     */
    public function getEpcQrCodeString(Invoice $invoice): ?string
    {
        if (empty($this->iban) || strtoupper($this->currency) !== 'EUR') {
            return null;
        }

        $amount = number_format($invoice->amount_due, 2, '.', '');
        $beneficiaryName = substr($this->name, 0, 70);
        $iban = str_replace(' ', '', $this->iban);
        $bic = str_replace(' ', '', $this->bic);
        $reference = substr($invoice->invoice_number, 0, 140);

        // EPC QR Code Format (V2):
        // BCD
        // 002 (Version)
        // 1 (Character set: 1=UTF-8)
        // SCT (Service ID)
        // [BIC] (Nullable)
        // [Name]
        // [IBAN]
        // EUR[Amount]
        // [Purpose Code] (Optional)
        // [Structured Reference] (Optional)
        // [Unstructured Reference] (Our Invoice Number)
        // [Notes] (Optional)

        $lines = [
            'BCD',
            '002',
            '1',
            'SCT',
            $bic,
            $beneficiaryName,
            $iban,
            'EUR'.$amount,
            '', // Purpose Code
            '', // Structured Reference
            $reference, // Unstructured Reference
            'Generated by InvoiceMaker', // Information
        ];

        return implode("\r\n", $lines);
    }
}
