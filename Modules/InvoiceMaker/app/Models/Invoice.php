<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Invoice extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_INVOICE = 'invoice';

    public const TYPE_ESTIMATE = 'estimate';

    protected $table = 'invoicemaker_invoices';

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'is_recurring' => 'boolean',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'scheduled_send_at' => 'datetime',
        'sent_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'public_viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revision_requested_at' => 'datetime',
        'inventory_deducted' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            $invoice->uuid ??= (string) Str::uuid();
            $invoice->created_by ??= auth()->id();
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'team_id', 'team_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(InvoiceComment::class);
    }

    public function isEstimate(): bool
    {
        return $this->type === self::TYPE_ESTIMATE;
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match (strtoupper($this->currency)) {
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'INR' => '₹',
            'PKR' => 'Rs ',
            'AED' => 'د.إ',
            'CAD', 'AUD', 'USD' => '$',
            default => $this->currency.' ',
        };
    }

    public function deductInventory(): void
    {
        if ($this->inventory_deducted || $this->isEstimate()) {
            return;
        }

        $this->loadMissing('items.product');

        foreach ($this->items as $item) {
            $item->product?->decrementStock((float) $item->quantity);
        }

        $this->update(['inventory_deducted' => true]);
    }
}
