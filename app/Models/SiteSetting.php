<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public function getValueAttribute($value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }

    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = is_array($value) || is_object($value) ? json_encode($value) : $value;
    }

    public static function value(string $key, mixed $default = null, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $cacheKey = 'site_setting_'.$key.'_'.$locale;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $setting = static::where('key', $key.'_'.$locale)->first();
        $value = $setting?->value;

        if ($value === null) {
            $base = static::where('key', $key)->first();
            $value = $base?->value;
        }

        if ($value !== null) {
            Cache::forever($cacheKey, $value);

            return $value;
        }

        return $default;
    }

    public static function set(string $key, mixed $value, ?string $locale = null): self
    {
        $locale ??= app()->getLocale();
        $cacheKey = 'site_setting_'.$key.'_'.$locale;
        Cache::forget($cacheKey);
        Cache::forget('site_setting_'.$key.'_'.config('app.fallback_locale', 'en'));

        return static::updateOrCreate(['key' => $key.'_'.$locale], ['value' => $value]);
    }
}
