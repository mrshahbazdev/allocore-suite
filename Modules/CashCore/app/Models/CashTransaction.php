<?php

namespace Modules\CashCore\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashTransaction extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'cashcore_category_id', 'type', 'amount', 'description',
        'vendor', 'transaction_date', 'is_recurring', 'recurring_interval', 'source', 'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'cashcore_category_id');
    }

    public function expenseScore(): HasOne
    {
        return $this->hasOne(CashExpenseScore::class, 'cashcore_transaction_id');
    }

    public function scopeIncome($query)
    {
        return $query->where('cashcore_transactions.type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('cashcore_transactions.type', 'expense');
    }

    public function scopeForPeriod($query, string $period)
    {
        $start = Carbon::parse($period.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $query->whereBetween('cashcore_transactions.transaction_date', [$start, $end]);
    }

    public function scopeRecurring($query)
    {
        return $query->where('cashcore_transactions.is_recurring', true);
    }
}
