<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDashboard extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'team_id', 'title', 'widgets', 'position', 'is_default'];

    protected function casts(): array
    {
        return [
            'widgets' => 'array',
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($model): void {
            if (! $model->team_id && auth()->user()?->current_team_id) {
                $model->team_id = auth()->user()->current_team_id;
            }
        });

        static::addGlobalScope('currentTeam', function ($builder) {
            if (auth()->user()?->current_team_id) {
                $builder->where('team_id', auth()->user()->current_team_id);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
