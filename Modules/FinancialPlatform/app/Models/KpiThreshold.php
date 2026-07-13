<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;

class KpiThreshold extends Model
{
    protected $table = 'financial_kpi_thresholds';

    protected $fillable = [
        'tool', 'kpi_code', 'kpi_name', 'unit',
        'green_min', 'green_max', 'yellow_min', 'yellow_max',
        'lower_is_better', 'weight', 'is_active',
    ];

    protected $casts = [
        'lower_is_better' => 'boolean',
        'is_active' => 'boolean',
        'green_min' => 'decimal:4',
        'green_max' => 'decimal:4',
        'yellow_min' => 'decimal:4',
        'yellow_max' => 'decimal:4',
        'weight' => 'decimal:2',
    ];

    public function evaluate(float $value): string
    {
        if ($this->lower_is_better) {
            if ($this->green_max !== null && $value <= (float) $this->green_max) {
                return 'green';
            }
            if ($this->yellow_max !== null && $value <= (float) $this->yellow_max) {
                return 'yellow';
            }

            return 'red';
        }

        if ($this->green_min !== null && $value >= (float) $this->green_min) {
            return 'green';
        }
        if ($this->yellow_min !== null && $value >= (float) $this->yellow_min) {
            return 'yellow';
        }

        return 'red';
    }
}
