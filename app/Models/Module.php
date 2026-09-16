<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'icon',
        'route_prefix',
        'is_active',
        'in_subscription_pool',
        'is_deprecated',
        'badge_text',
        'sort_order',
        'allowed_roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'in_subscription_pool' => 'boolean',
        'is_deprecated' => 'boolean',
        'sort_order' => 'integer',
        'allowed_roles' => 'array',
    ];

    public static function byKey(string $key): ?self
    {
        return self::where('key', $key)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deprecated', false);
    }

    public function scopeInSubscriptionPool($query)
    {
        return $query->where('in_subscription_pool', true)->where('is_active', true)->where('is_deprecated', false);
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
