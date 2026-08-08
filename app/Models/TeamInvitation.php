<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TeamInvitation extends Model
{
    protected $fillable = [
        'team_id', 'project_id', 'invited_by', 'email', 'role', 'project_role', 'token', 'accepted_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public static function generateToken(): string
    {
        return hash('sha256', Str::random(40));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function projectName(): ?string
    {
        if (! $this->project_id) {
            return null;
        }

        $project = DB::table('planhive_projects')->find($this->project_id);

        return $project?->name;
    }

    public function accept(User $user): void
    {
        $this->team->members()->syncWithoutDetaching([$user->id => ['role' => $this->role]]);

        Role::findOrCreate('regular-user');
        Role::findOrCreate('employee');
        $user->assignRole(['regular-user', 'employee']);

        if ($this->project_id) {
            DB::table('planhive_project_members')->updateOrInsert(
                ['project_id' => $this->project_id, 'user_id' => $user->id],
                ['role' => $this->project_role ?: 'member', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->update(['accepted_at' => now()]);
    }
}
