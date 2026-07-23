<?php

namespace Modules\SweetSpot\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SweetSpot\Models\Concerns\BelongsToCurrentTeam;

class CustomerScore extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'sweet_spot_customer_scores';

    protected $fillable = [
        'customer_id',
        'team_id',
        'margin_per_hour',
        'profitability_score',
        'effort_score',
        'chemistry_score',
        'growth_score',
        'repeat_score',
        'recommendation_score',
        'payment_score',
        'total_score',
        'rank',
        'top_flag',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'top_flag' => 'boolean',
            'calculated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
