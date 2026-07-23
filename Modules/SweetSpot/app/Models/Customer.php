<?php

namespace Modules\SweetSpot\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\SweetSpot\Models\Concerns\BelongsToCurrentTeam;

class Customer extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'sweet_spot_customers';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'industry',
        'company_size',
        'revenue',
        'profit_margin_eur',
        'margin_percent',
        'effort_hours',
        'chemistry_score',
        'growth_score',
        'repeat_rate',
        'recommendations',
        'payment_willingness',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'revenue' => 'decimal:2',
            'profit_margin_eur' => 'decimal:2',
            'margin_percent' => 'decimal:2',
            'effort_hours' => 'decimal:2',
            'repeat_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(CustomerScore::class, 'customer_id');
    }

    protected static function booted(): void
    {
        static::deleting(function ($customer): void {
            if ($customer->score) {
                $customer->score->delete();
            }
        });
    }
}
