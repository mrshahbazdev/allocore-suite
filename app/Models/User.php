<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'current_team_id', 'is_active', 'locale', 'theme', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'theme' => 'string',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot(['role', 'allowed_modules'])->withTimestamps();
    }

    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    public function currentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'current_team_id');
    }

    public function mailSetting(): HasOne
    {
        return $this->hasOne(MailSetting::class);
    }

    public function toolSubscriptions(): MorphMany
    {
        return $this->morphMany(ToolSubscription::class, 'billable');
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'causer');
    }

    public function activeSubscriptions()
    {
        return $this->toolSubscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function hasModule(string $moduleKey): bool
    {
        $ownAccess = $this->activeSubscriptions()
            ->whereHas('plan.modules', fn ($q) => $q->where('key', $moduleKey))
            ->exists();

        if ($ownAccess && ! $this->currentTeam) {
            return true;
        }

        if ($this->currentTeam?->hasModule($moduleKey) && $this->isAllowedModule($moduleKey)) {
            return true;
        }

        return $ownAccess && $this->isAllowedModule($moduleKey);
    }

    protected function isAllowedModule(string $moduleKey): bool
    {
        if (! $this->current_team_id) {
            return true;
        }

        $membership = $this->teams()->where('teams.id', $this->current_team_id)->first();

        if (! $membership || $membership->pivot->role === 'owner') {
            return true;
        }

        $allowed = $membership->pivot->allowed_modules;

        if ($allowed === null) {
            return true;
        }

        return in_array($moduleKey, json_decode($allowed, true) ?: [], true);
    }

    public function accessibleModules()
    {
        return Module::where('is_active', true)
            ->get()
            ->filter(fn (Module $module) => $this->hasModule($module->key))
            ->values();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
