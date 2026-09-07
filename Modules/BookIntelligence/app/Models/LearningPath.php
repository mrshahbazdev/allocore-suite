<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class LearningPath extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED = 'paused';

    protected $table = 'bookintelligence_learning_paths';

    protected $fillable = [
        'team_id',
        'user_id',
        'target_role_id',
        'title',
        'status',
        'steps',
        'progress_percent',
        'ai_rationale',
    ];

    protected $casts = [
        'steps' => 'array',
        'progress_percent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetRole(): BelongsTo
    {
        return $this->belongsTo(CompetencyRole::class, 'target_role_id');
    }
}
