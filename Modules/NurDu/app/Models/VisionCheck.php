<?php

namespace Modules\NurDu\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\NurDu\Models\Concerns\BelongsToCurrentTeam;

class VisionCheck extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'nurdu_vision_checks';

    protected $fillable = ['team_id', 'user_id', 'check_date', 'q1_answer', 'q2_answer', 'q3_answer', 'notes'];

    protected $casts = [
        'check_date' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionItems(): HasMany
    {
        return $this->hasMany(ActionItem::class);
    }
}
