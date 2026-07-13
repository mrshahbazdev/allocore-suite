<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class JahresabschlussInput extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_jahresabschluss_inputs';

    protected $fillable = [
        'team_id', 'user_id', 'analysis_id', 'year_label', 'year_order',
        'cash', 'receivables', 'inventory', 'other_current_assets',
        'current_assets', 'fixed_assets', 'total_assets',
        'equity', 'current_liabilities', 'long_term_liabilities',
        'total_liabilities', 'payables',
        'revenue', 'material_costs', 'personnel_costs',
        'depreciation', 'other_opex', 'ebit',
        'interest_exp', 'ebt', 'taxes', 'net_profit',
    ];

    protected $casts = [
        'year_order' => 'integer',
        'cash' => 'decimal:2', 'receivables' => 'decimal:2',
        'inventory' => 'decimal:2', 'current_assets' => 'decimal:2',
        'fixed_assets' => 'decimal:2', 'total_assets' => 'decimal:2',
        'equity' => 'decimal:2', 'current_liabilities' => 'decimal:2',
        'long_term_liabilities' => 'decimal:2', 'total_liabilities' => 'decimal:2',
        'payables' => 'decimal:2', 'revenue' => 'decimal:2',
        'material_costs' => 'decimal:2', 'personnel_costs' => 'decimal:2',
        'depreciation' => 'decimal:2', 'ebit' => 'decimal:2',
        'interest_exp' => 'decimal:2', 'net_profit' => 'decimal:2',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
