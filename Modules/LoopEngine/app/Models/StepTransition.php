<?php

namespace Modules\LoopEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class StepTransition extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_step_transitions';

    protected $fillable = [
        'team_id',
        'step_id',
        'option_id',
        'action_type',
        'target_step_id',
        'target_process_id',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(StepOption::class, 'option_id');
    }

    public function targetStep(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'target_step_id');
    }

    public function targetProcess(): BelongsTo
    {
        return $this->belongsTo(Process::class, 'target_process_id');
    }
}
