<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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

#[Fillable(['name', 'email', 'password', 'current_team_id', 'is_active', 'locale', 'theme', 'onboarding_step', 'onboarding_completed_at', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasRoles, MustVerifyEmailTrait, Notifiable;

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
            'onboarding_completed_at' => 'datetime',
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

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function notificationPreference(string $type = 'general'): NotificationPreference
    {
        return $this->notificationPreferences()->firstOrCreate(['type' => $type], [
            'email' => true,
            'in_app' => true,
            'push' => true,
            'slack' => false,
        ]);
    }

    public function activeSubscriptions()
    {
        return $this->toolSubscriptions()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function hasModule(string $moduleKey): bool
    {
        $module = Module::byKey($moduleKey);

        if (! $module) {
            return false;
        }

        $ownAccess = $this->activeSubscriptions()
            ->whereHas('plan.modules', fn ($q) => $q->where('key', $moduleKey))
            ->exists();

        $teamAccess = $this->currentTeam?->hasModule($moduleKey) ?? false;

        if (! $ownAccess && ! $teamAccess) {
            return false;
        }

        return $this->isAllowedModule($module);
    }

    protected function isAllowedModule(Module $module): bool
    {
        if ($this->isAdmin() || $this->isOwner()) {
            return true;
        }

        if ($this->current_team_id) {
            $membership = $this->teams()->where('teams.id', $this->current_team_id)->first();

            if ($membership?->pivot->role === 'owner') {
                return true;
            }

            $allowed = $membership?->pivot->allowed_modules;

            if ($allowed !== null) {
                return in_array($module->key, json_decode($allowed, true) ?: [], true);
            }
        }

        return $this->canAccessModuleByRole($module);
    }

    protected function canAccessModuleByRole(Module $module): bool
    {
        $allowedRoles = $module->allowed_roles;

        if (empty($allowedRoles)) {
            return true;
        }

        return $this->hasAnyRole($allowedRoles);
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

    public function isOwner(): bool
    {
        return $this->hasRole('owner') || ($this->current_team_id && $this->ownedTeams()->where('id', $this->current_team_id)->exists());
    }
}
