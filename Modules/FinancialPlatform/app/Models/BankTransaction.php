<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class BankTransaction extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_bank_transactions';

    protected $fillable = [
        'team_id',
        'user_id',
        'transaction_date',
        'description',
        'amount',
        'currency',
        'type',
        'category',
        'import_source',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
