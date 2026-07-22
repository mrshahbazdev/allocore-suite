<?php

namespace Modules\FocusMatrix\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FocusMatrix\Models\Concerns\BelongsToCurrentTeam;

class SelfCheck extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'focusmatrix_self_checks';

    protected $fillable = [
        'team_id',
        'user_id',
        'year',
        'week',
        'q1_others_could_do',
        'q2_delegated_late',
        'q3_to_omit_next_week',
        'q4_focused_decisions',
        'focus_score',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
