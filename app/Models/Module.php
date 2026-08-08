<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = ['key', 'name', 'description', 'icon', 'route_prefix', 'is_active', 'allowed_roles'];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_roles' => 'array',
    ];

    public static function byKey(string $key): ?self
    {
        return self::where('key', $key)->first();
    }

    public function getNameAttribute(?string $value): ?string
    {
        return $value ? __($value) : null;
    }

    public function getDescriptionAttribute(?string $value): ?string
    {
        return $value ? __($value) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class)->withTimestamps();
    }
}
