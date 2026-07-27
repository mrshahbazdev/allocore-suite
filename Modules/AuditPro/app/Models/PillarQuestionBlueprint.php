<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;

class PillarQuestionBlueprint extends Model
{
    protected $table = 'auditpro_pillar_question_blueprints';

    protected $fillable = [
        'pillar',
        'position',
        'question',
        'description',
        'recommendation',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeForPillar($query, string $pillar)
    {
        return $query->where('pillar', $pillar)->where('is_active', true)->orderBy('position');
    }
}
