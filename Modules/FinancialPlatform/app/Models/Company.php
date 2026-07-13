<?php

namespace Modules\FinancialPlatform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class Company extends Model
{
    use BelongsToCurrentTeam;
    use SoftDeletes;

    protected $table = 'financial_companies';

    protected $fillable = [
        'team_id', 'user_id', 'name', 'industry', 'currency', 'country', 'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
