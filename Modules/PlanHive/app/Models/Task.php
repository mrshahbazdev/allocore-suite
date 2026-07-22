<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Task extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_tasks';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }
}
