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
