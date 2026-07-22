<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\DentalTrack\Enums\WorkstationType;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class Workstation extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_workstations';

    protected $fillable = [
        'team_id', 'dentaltrack_lab_id', 'name', 'qr_code', 'type', 'is_active',
    ];

    protected $casts = [
        'type' => WorkstationType::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->qr_code)) {
                $prefix = $model->type === WorkstationType::WaitingArea ? 'WA-' : 'WS-';
                $model->qr_code = $prefix.Str::ulid();
            }
        });
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'dentaltrack_lab_id');
    }

    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'dentaltrack_workstation_id');
    }

    public function qrUrl(): string
    {
        return url('/dentaltrack/scan/'.$this->qr_code);
    }
}
