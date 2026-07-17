<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'current_team_id'])]
#[Hidden(['password', 'remember_token'])]
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
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role')->withTimestamps();
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

        if ($ownAccess) {
            return true;
        }

        return $this->currentTeam?->hasModule($moduleKey) ?? false;
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
