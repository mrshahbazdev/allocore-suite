<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class Prediction extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_predictions';

    protected $fillable = [
        'team_id', 'dentaltrack_order_id', 'model_version', 'predicted_minutes', 'actual_minutes', 'accuracy_pct',
    ];

    protected $casts = [
        'predicted_minutes' => 'integer',
        'actual_minutes' => 'integer',
        'accuracy_pct' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'dentaltrack_order_id');
    }
}
