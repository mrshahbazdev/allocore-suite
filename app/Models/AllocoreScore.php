<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AuditPro\Models\Audit;

class AllocoreScore extends Model
{
    protected $fillable = [
        'team_id',
        'audit_id',
        'company_name',
        'industry',
        'industry_sub',
        'size',
        'company_age',
        'score',
        'maturity_level',
        'pillars',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'pillars' => 'array',
            'calculated_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }
}
