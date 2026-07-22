<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\DentalTrack\Enums\QrPrintFormat;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class QrPrintJob extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_qr_print_jobs';

    protected $fillable = [
        'team_id', 'printable_type', 'printable_id', 'format', 'printed_at',
    ];

    protected $casts = [
        'format' => QrPrintFormat::class,
        'printed_at' => 'datetime',
    ];

    public function printable(): MorphTo
    {
        return $this->morphTo();
    }
}
