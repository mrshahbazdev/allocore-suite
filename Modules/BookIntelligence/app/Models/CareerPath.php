<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class CareerPath extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_career_paths';

    protected $fillable = [
        'team_id',
        'from_role_id',
        'to_role_id',
        'title',
        'description',
        'required_competencies',
        'recommended_book_ids',
        'milestones',
    ];

    protected $casts = [
        'required_competencies' => 'array',
        'recommended_book_ids' => 'array',
        'milestones' => 'array',
    ];

    public function fromRole(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'from_role_id');
    }

    public function toRole(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'to_role_id');
    }
}
