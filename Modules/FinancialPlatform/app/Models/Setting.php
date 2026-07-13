<?php

namespace Modules\FinancialPlatform\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\FinancialPlatform\Models\Concerns\BelongsToCurrentTeam;

class Setting extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'financial_settings';

    protected $fillable = ['team_id', 'key', 'value', 'type'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string'): static
    {
        $valToStore = is_array($value) ? json_encode($value) : $value;

        return static::updateOrCreate(
            ['team_id' => auth()->user()?->current_team_id, 'key' => $key],
            ['value' => $valToStore, 'type' => $type]
        );
    }
}
