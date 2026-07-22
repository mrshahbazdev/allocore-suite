<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashExpenseScore extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'cashcore_transaction_id', 'purpose', 'benefit',
        'revenue_score', 'efficiency_score', 'strategic_score', 'usage_score',
        'total_score', 'recommendation',
    ];

    protected $table = 'cashcore_expense_scores';

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'cashcore_transaction_id');
    }

    public function calculateTotal(): int
    {
        $this->total_score = $this->revenue_score + $this->efficiency_score
            + $this->strategic_score + $this->usage_score;

        if ($this->total_score >= 28) {
            $this->recommendation = 'keep';
        } elseif ($this->total_score >= 15) {
            $this->recommendation = 'reduce';
        } else {
            $this->recommendation = 'eliminate';
        }

        return $this->total_score;
    }
}
