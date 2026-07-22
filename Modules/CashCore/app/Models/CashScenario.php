<?php

namespace Modules\CashCore\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\CashCore\Models\Concerns\BelongsToCurrentTeam;

class CashScenario extends Model
{
    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'user_id', 'name', 'description', 'current_revenue',
        'current_costs', 'adjusted_revenue', 'adjusted_costs',
        'projected_profit', 'adjustments',
    ];

    protected $casts = [
        'current_revenue' => 'float',
        'current_costs' => 'float',
        'adjusted_revenue' => 'float',
        'adjusted_costs' => 'float',
        'projected_profit' => 'float',
        'adjustments' => 'array',
    ];

    protected $table = 'cashcore_scenarios';

    public function calculateProjectedProfit(): float
    {
        $this->projected_profit = $this->adjusted_revenue - $this->adjusted_costs;

        return (float) $this->projected_profit;
    }

    public function currentProfit(): float
    {
        return (float) ($this->current_revenue - $this->current_costs);
    }

    public function profitDelta(): float
    {
        return $this->calculateProjectedProfit() - $this->currentProfit();
    }
}
