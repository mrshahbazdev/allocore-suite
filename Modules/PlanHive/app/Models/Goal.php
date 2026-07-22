<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Goal extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_goals';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'title',
        'description',
        'target_date',
        'progress',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'progress' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
