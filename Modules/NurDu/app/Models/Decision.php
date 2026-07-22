<?php

namespace Modules\NurDu\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class Decision extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_decisions';

    protected $fillable = ['team_id', 'user_id', 'title', 'description', 'alignment', 'justification', 'decision_date'];

    protected $casts = [
        'decision_date' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
