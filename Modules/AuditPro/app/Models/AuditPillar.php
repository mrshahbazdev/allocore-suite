<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class AuditPillar extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'auditpro_pillars';

    protected $fillable = [
        'team_id',
        'template_id',
        'name',
        'description',
        'icon',
        'target_score',
        'position',
    ];

    protected function casts(): array
    {
        return ['target_score' => 'decimal:1'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AuditQuestion::class, 'pillar_id')->orderBy('position');
    }
}
