<?php

namespace Modules\TimeButler\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\TimeButler\Models\Concerns\BelongsToCurrentTeam;

class AbsenceRequest extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'timebutler_absence_requests';

    protected $fillable = [
        'team_id',
        'user_id',
        'absence_type_id',
        'start_date',
        'end_date',
        'half_day_start',
        'half_day_end',
        'total_days',
        'status',
        'substitute_id',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'certificate_path',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'half_day_start' => 'boolean',
            'half_day_end' => 'boolean',
            'total_days' => 'decimal:1',
            'approved_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }

    public function substitute(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function absenceDays(): HasMany
    {
        return $this->hasMany(AbsenceDay::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
