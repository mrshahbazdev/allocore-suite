<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Team extends Model
{
    protected $fillable = ['name', 'owner_id', 'industry', 'size'];

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
}
