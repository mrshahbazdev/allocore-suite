<?php

namespace Modules\LoopEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class ProcessRun extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_process_runs';

    protected $fillable = [
        'team_id',
        'process_id',
        'started_by',
        'assigned_to',
        'status',
        'current_step_id',
        'loop_count',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'loop_count' => 'integer',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'current_step_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RunResponse::class, 'run_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RunLog::class, 'run_id');
    }
}
