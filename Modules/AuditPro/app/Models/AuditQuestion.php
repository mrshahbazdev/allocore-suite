<?php

namespace Modules\AuditPro\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AuditPro\Models\Concerns\BelongsToCurrentTeam;

class AuditQuestion extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'auditpro_questions';

    protected $fillable = [
        'team_id',
        'template_id',
        'pillar_id',
        'question',
        'description',
        'question_type',
        'weight',
        'is_required',
        'failure_recommendation',
        'options',
        'depends_on_question_id',
        'depends_on_answer',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'template_id');
    }

    public function pillar(): BelongsTo
    {
        return $this->belongsTo(AuditPillar::class, 'pillar_id');
    }

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(self::class, 'depends_on_question_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(self::class, 'depends_on_question_id');
    }
}
