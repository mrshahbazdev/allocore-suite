<?php

namespace Modules\OrgMatrix\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\OrgMatrix\Models\Concerns\BelongsToCurrentTeam;

class Organization extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'orgmatrix_organizations';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'industry',
        'logo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function rootRoles(): HasMany
    {
        return $this->hasMany(Role::class)->whereNull('parent_role_id');
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
