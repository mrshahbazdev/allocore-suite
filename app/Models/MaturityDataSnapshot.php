<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaturityDataSnapshot extends Model
{
    protected $fillable = [
        'team_id',
        'audit_id',
        'allocore_score_id',
        'company_name',
        'industry',
        'industry_sub',
        'size',
        'company_age',
        'country',
        'revenue_range',
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
            'company_age' => 'integer',
            'calculated_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function allocoreScore(): BelongsTo
    {
        return $this->belongsTo(AllocoreScore::class, 'allocore_score_id');
    }
}
