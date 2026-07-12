<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class AuditResult extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'auditpro_results';

    protected $fillable = [
        'team_id',
        'audit_id',
        'pillar_id',
        'level',
        'average_score',
        'maturity_level',
        'total_points',
    ];

    protected function casts(): array
    {
        return [
            'average_score' => 'decimal:2',
            'total_points' => 'decimal:2',
        ];
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class);
    }

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(AuditPillar::class);
    }
}
