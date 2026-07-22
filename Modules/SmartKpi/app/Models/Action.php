<?php

namespace Modules\SmartKpi\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\SmartKpi\Models\Concerns\BelongsToCurrentTeam;

class Action extends Model
{
    protected $table = 'smartkpi_actions';

    use BelongsToCurrentTeam;

    protected $fillable = [
        'team_id', 'problem_id', 'assigned_to', 'title', 'description', 'priority', 'due_date', 'status', 'effectiveness_score',
    ];

    protected $casts = [
        'due_date' => 'date',
        'effectiveness_score' => 'integer',
    ];

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
