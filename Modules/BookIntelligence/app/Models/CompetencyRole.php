<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class CompetencyRole extends Model
{
    use BelongsToCurrentTeam;

    public const LEVELS = ['starter', 'junior', 'professional', 'senior', 'expert', 'master'];
    public const DEPARTMENTS = ['Sales', 'Operations', 'Customer Success', 'Management', 'Leadership', 'Revenue Operations', 'Product'];

    protected $table = 'bookintelligence_competency_roles';

    protected $fillable = [
        'team_id',
        'name',
        'slug',
        'department',
        'level',
        'description',
        'required_knowledge',
        'required_competencies',
        'required_skills',
        'next_role_id',
    ];

    protected $casts = [
        'required_knowledge' => 'array',
        'required_competencies' => 'array',
        'required_skills' => 'array',
    ];

    public function nextRole(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_role_id');
    }

    public function careerPathsFrom(): HasMany
    {
        return $this->hasMany(CareerPath::class, 'from_role_id');
    }

    public function careerPathsTo(): HasMany
    {
        return $this->hasMany(CareerPath::class, 'to_role_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(SkillAssessment::class, 'role_id');
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(PracticalChallenge::class, 'role_id');
    }
}
