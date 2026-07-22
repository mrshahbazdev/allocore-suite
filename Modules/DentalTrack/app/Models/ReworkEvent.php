<?php

namespace Modules\DentalTrack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DentalTrack\Enums\ReworkCause;
use Modules\DentalTrack\Enums\ReworkStatus;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class ReworkEvent extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_rework_events';

    protected $fillable = [
        'team_id', 'dentaltrack_order_id', 'dentaltrack_order_step_id', 'flagged_by',
        'original_technician', 'cause', 'description', 'status', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'cause' => ReworkCause::class,
        'status' => ReworkStatus::class,
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'dentaltrack_order_id');
    }

    public function orderStep(): BelongsTo
    {
        return $this->belongsTo(OrderStep::class, 'dentaltrack_order_step_id');
    }

    public function flaggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'flagged_by');
    }

    public function originalTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'original_technician');
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
