<?php

namespace Modules\SweetSpot\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\SweetSpot\Models\Concerns\BelongsToCurrentTeam;

class SettingsWeight extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'sweet_spot_settings_weights';

    protected $fillable = [
        'team_id',
        'criterion_key',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
        ];
    }
}
