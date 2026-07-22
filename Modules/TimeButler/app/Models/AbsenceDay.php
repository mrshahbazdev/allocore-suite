<?php

namespace Modules\TimeButler\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceDay extends Model
{
    protected $table = 'timebutler_absence_days';

    protected $fillable = [
        'absence_request_id',
        'date',
        'half_day',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'half_day' => 'boolean',
        ];
    }

    public function absenceRequest(): BelongsTo
    {
        return $this->belongsTo(AbsenceRequest::class);
    }
}
