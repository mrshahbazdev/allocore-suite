<?php

namespace Modules\InvoiceMaker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\InvoiceMaker\Models\Concerns\BelongsToCurrentTeam;

class AccountingCategory extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'invoicemaker_accounting_categories';

    protected $guarded = [];

    public function getEffectiveCostTypeAttribute(): string
    {
        if (!empty($this->cost_type)) {
            return $this->cost_type;
        }

        $name = strtolower((string) $this->name);
        $fixedKeywords = [
            'rent', 'miete', 'software', 'saas', 'license', 'lizenz', 'subscription', 'abo',
            'insurance', 'versich', 'salary', 'salaries', 'gehalt', 'lohn', 'hosting', 'server',
            'internet', 'phone', 'telefon', 'strom', 'electricity', 'tax advisor', 'steuerberater',
            'fixed', 'fix'
        ];

        foreach ($fixedKeywords as $keyword) {
            if (str_contains($name, $keyword)) {
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

    public function business(): BelongsTo
    {
        return $this->profile();
    }

    public function cashBookEntries(): HasMany
    {
        return $this->hasMany(CashBookEntry::class, 'category_id');
    }

    public function cash_book_entries(): HasMany
    {
        return $this->cashBookEntries();
    }
}
