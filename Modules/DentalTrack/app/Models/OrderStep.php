<?php

namespace Modules\DentalTrack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\DentalTrack\Enums\StepStatus;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class OrderStep extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_order_steps';

    protected $fillable = [
        'team_id', 'dentaltrack_order_id', 'dentaltrack_process_template_id', 'sort_order',
        'step_name', 'status', 'assigned_to',
    ];

    protected $casts = [
        'status' => StepStatus::class,
        'sort_order' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'dentaltrack_order_id');
    }

    public function processTemplate(): BelongsTo
    {
        return $this->belongsTo(ProcessTemplate::class, 'dentaltrack_process_template_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'dentaltrack_order_step_id');
    }

    public function totalDurationSeconds(): int
    {
        return (int) $this->scanEvents()->sum('duration_seconds');
    }

    public function reworkEvents(): HasMany
    {
        return $this->hasMany(ReworkEvent::class, 'dentaltrack_order_step_id');
    }
}
