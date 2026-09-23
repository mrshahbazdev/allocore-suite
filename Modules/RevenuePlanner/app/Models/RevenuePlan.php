<?php

namespace Modules\RevenuePlanner\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\RevenuePlanner\Models\Concerns\BelongsToCurrentTeam;

class RevenuePlan extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'revenue_planner_plans';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'industry_type',
        'fiscal_year',
        'fixed_costs_annual',
        'fixed_costs_monthly',
        'fixed_costs_breakdown',
        'owner_compensation_annual',
        'owner_compensation_monthly',
        'owner_compensation_breakdown',
        'profit_target_annual',
        'tax_rate_percent',
        'contribution_margin_percent',
        'target_revenue_annual',
        'target_revenue_monthly',
        'actual_revenue_annual',
        'actual_revenue_monthly',
        'revenue_gap_annual',
        'revenue_gap_monthly',
        'coverage_ratio_percent',
        'existing_customer_potential_annual',
        'existing_customer_levers',
        'remaining_gap_annual',
        'remaining_gap_monthly',
        'average_deal_size',
        'lead_to_close_rate_percent',
        'required_won_deals_annual',
        'required_won_deals_monthly',
        'required_leads_annual',
        'required_leads_monthly',
        'seasonality_pattern',
        'monthly_distribution',
        'action_roadmap',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'fixed_costs_annual' => 'decimal:2',
            'fixed_costs_monthly' => 'decimal:2',
            'fixed_costs_breakdown' => 'array',
            'owner_compensation_annual' => 'decimal:2',
            'owner_compensation_monthly' => 'decimal:2',
            'owner_compensation_breakdown' => 'array',
            'profit_target_annual' => 'decimal:2',
            'tax_rate_percent' => 'decimal:2',
            'contribution_margin_percent' => 'decimal:2',
            'target_revenue_annual' => 'decimal:2',
            'target_revenue_monthly' => 'decimal:2',
            'actual_revenue_annual' => 'decimal:2',
            'actual_revenue_monthly' => 'decimal:2',
            'revenue_gap_annual' => 'decimal:2',
            'revenue_gap_monthly' => 'decimal:2',
            'coverage_ratio_percent' => 'decimal:2',
            'existing_customer_potential_annual' => 'decimal:2',
            'existing_customer_levers' => 'array',
            'remaining_gap_annual' => 'decimal:2',
            'remaining_gap_monthly' => 'decimal:2',
            'average_deal_size' => 'decimal:2',
            'lead_to_close_rate_percent' => 'decimal:2',
            'required_won_deals_annual' => 'integer',
            'required_won_deals_monthly' => 'integer',
            'required_leads_annual' => 'integer',
            'required_leads_monthly' => 'integer',
            'monthly_distribution' => 'array',
            'action_roadmap' => 'array',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scenarios(): HasMany
    {
        return $this->hasMany(RevenueScenario::class, 'plan_id');
    }
}
