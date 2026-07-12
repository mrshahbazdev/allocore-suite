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

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
