<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PillarQuestionBlueprint extends Model
{
    protected $table = 'auditpro_pillar_question_blueprints';

    protected $fillable = [
        'pillar',
        'parent_id',
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

    public function scopeMains($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeFollowUps($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }
}
