<?php

namespace Modules\NurDu\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class Vision extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_visions';

    protected $fillable = ['team_id', 'user_id', 'statement'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guidingPrinciples(): HasMany
    {
        return $this->hasMany(GuidingPrinciple::class)->orderBy('sort_order');
    }
}
