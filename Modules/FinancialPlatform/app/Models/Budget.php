<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class Budget extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_budgets';

    protected $fillable = [
        'team_id',
        'user_id',
        'category',
        'year',
        'month',
        'amount',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
