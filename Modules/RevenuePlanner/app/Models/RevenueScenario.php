<?php

namespace Modules\RevenuePlanner\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\RevenuePlanner\Models\Concerns\BelongsToCurrentTeam;

class RevenueScenario extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'revenue_planner_scenarios';

    protected $fillable = [
        'team_id',
        'user_id',
        'plan_id',
        'title',
        'description',
        'price_increase_percent',
        'deal_size_multiplier',
        'conversion_rate_delta_percent',
        'lead_volume_multiplier',
        'simulated_annual_revenue',
        'simulated_revenue_gap',
        'simulated_required_leads',
        'simulated_monthly_distribution',
    ];

    protected function casts(): array
    {
        return [
            'price_increase_percent' => 'decimal:2',
            'deal_size_multiplier' => 'decimal:2',
            'conversion_rate_delta_percent' => 'decimal:2',
            'lead_volume_multiplier' => 'decimal:2',
            'simulated_annual_revenue' => 'decimal:2',
            'simulated_revenue_gap' => 'decimal:2',
            'simulated_required_leads' => 'integer',
            'simulated_monthly_distribution' => 'array',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(RevenuePlan::class, 'plan_id');
    }
}
