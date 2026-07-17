<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key', 'locale', 'type', 'subject', 'body', 'variables', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function findByKey(string $key, string $type = 'email', ?string $locale = null): ?self
    {
        $locale ??= app()->getLocale();

        return static::where('key', $key)
            ->where('type', $type)
            ->where('locale', $locale)
            ->where('is_active', true)
            ->first();
    }
}
