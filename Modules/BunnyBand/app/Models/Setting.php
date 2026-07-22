<?php

namespace Modules\BunnyBand\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\BunnyBand\Models\Concerns\BelongsToCurrentTeam;

class Setting extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bunnyband_settings';

    protected $fillable = [
        'team_id', 'key', 'value',
    ];

    public static function get(int $teamId, string $key, $default = null): mixed
    {
        $setting = self::where('team_id', $teamId)->where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(int $teamId, string $key, $value): void
    {
        self::updateOrCreate(['team_id' => $teamId, 'key' => $key], ['value' => $value]);
    }
}
