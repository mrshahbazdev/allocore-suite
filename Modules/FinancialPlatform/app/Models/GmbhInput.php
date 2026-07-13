<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class GmbhInput extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_gmbh_inputs';

    protected $fillable = [
        'team_id', 'user_id', 'analysis_id',
        'revenue_current', 'revenue_prev',
        'cogs', 'personnel', 'other_opex',
        'ebitda', 'depreciation', 'interest', 'net_profit',
        'total_assets', 'equity', 'total_debt',
        'current_liabilities', 'current_assets',
        'cash', 'monthly_burn',
        'cac', 'ltv',
        'mgmt_score', 'market_score',
        'custom_weights',
    ];

    protected $casts = [
        'revenue_current' => 'decimal:2',
        'revenue_prev' => 'decimal:2',
        'cogs' => 'decimal:2',
        'personnel' => 'decimal:2',
        'ebitda' => 'decimal:2',
        'depreciation' => 'decimal:2',
        'interest' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'total_assets' => 'decimal:2',
        'equity' => 'decimal:2',
        'total_debt' => 'decimal:2',
        'current_liabilities' => 'decimal:2',
        'current_assets' => 'decimal:2',
        'cash' => 'decimal:2',
        'monthly_burn' => 'decimal:2',
        'cac' => 'decimal:2',
        'ltv' => 'decimal:2',
        'custom_weights' => 'array',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
