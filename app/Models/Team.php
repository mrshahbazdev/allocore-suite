<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Team extends Model
{
    protected $fillable = [
        'name', 'owner_id', 'industry', 'size',
        'subdomain', 'custom_domain', 'custom_domain_verified_at',
        'ssl_status', 'ssl_issued_at', 'ssl_expires_at', 'ssl_last_error',
        'logo', 'favicon',
        'primary_color', 'accent_color', 'requires_two_factor',
    ];

    protected function casts(): array
    {
        return [
            'requires_two_factor' => 'boolean',
            'custom_domain_verified_at' => 'datetime',
            'ssl_issued_at' => 'datetime',
            'ssl_expires_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role', 'allowed_modules'])->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function toolSubscriptions(): MorphMany
    {
        return $this->morphMany(ToolSubscription::class, 'billable');
    }

    public function activeSubscriptions()
    {
        return $this->toolSubscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function hasModule(string $moduleKey): bool
    {
        $teamSubscription = $this->activeSubscriptions()
            ->whereHas('plan.modules', fn ($q) => $q->where('key', $moduleKey))
            ->exists();

        $ownerSubscription = $this->owner?->activeSubscriptions()
            ->whereHas('plan.modules', fn ($q) => $q->where('key', $moduleKey))
            ->exists() ?? false;

        return $teamSubscription || $ownerSubscription;
    }

    public function branding(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->logo,
            'favicon' => $this->favicon,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
            'custom_domain' => $this->custom_domain,
            'custom_domain_verified_at' => $this->custom_domain_verified_at,
            'ssl_status' => $this->ssl_status,
            'ssl_expires_at' => $this->ssl_expires_at,
        ];
    }
}
