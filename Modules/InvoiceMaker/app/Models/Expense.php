<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class Expense extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_expenses';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function getEffectiveCostTypeAttribute(): string
    {
        if (!empty($this->cost_type)) {
            return $this->cost_type;
        }

        if ($this->category_id && $this->accounting_category?->cost_type) {
            return $this->accounting_category->cost_type;
        }

        $catName = strtolower((string) ($this->category ?? $this->accounting_category?->name ?? ''));
        $desc = strtolower((string) ($this->description ?? ''));
        $target = $catName.' '.$desc;

        $fixedKeywords = [
            'rent', 'miete', 'software', 'saas', 'license', 'lizenz', 'subscription', 'abo',
            'insurance', 'versich', 'salary', 'salaries', 'gehalt', 'lohn', 'hosting', 'server',
            'internet', 'phone', 'telefon', 'strom', 'electricity', 'tax advisor', 'steuerberater',
            'fixed', 'fix'
        ];

        foreach ($fixedKeywords as $keyword) {
            if (str_contains($target, $keyword)) {
                return 'fixed';
            }
        }

        return 'variable';
    }

    public function isFixedCost(): bool
    {
        return $this->effective_cost_type === 'fixed';
    }

    public function isVariableCost(): bool
    {
        return $this->effective_cost_type === 'variable';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function networkInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'network_invoice_id');
    }

    public function accountingCategory(): BelongsTo
    {
        return $this->category();
    }

    public function accounting_category(): BelongsTo
    {
        return $this->category();
    }

    public function business(): BelongsTo
    {
        return $this->profile();
    }
}
