<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class Lead extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_leads';

    protected $fillable = [
        'team_id', 'user_id',
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'position',
        'linkedin',
        'website',
        'source',
        'status',
        'priority',
        'industry',
        'budget',
        'notes',
        'transferred_to_leados',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'transferred_to_leados' => 'boolean',
            'transferred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function paypalTransactions(): HasMany
    {
        return $this->hasMany(PaypalTransaction::class);
    }
}
