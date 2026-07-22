<?php

namespace Modules\FocusMatrix\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\FocusMatrix\Models\Concerns\BelongsToCurrentTeam;

class KillListItem extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'focusmatrix_kill_list_items';

    protected $fillable = [
        'team_id',
        'user_id',
        'task_id',
        'item_type',
        'title',
        'reason',
        'was_necessary',
        'served_clear_goal',
        'anything_missing',
        'killed_at',
    ];

    protected $casts = [
        'killed_at' => 'datetime',
        'was_necessary' => 'boolean',
        'served_clear_goal' => 'boolean',
        'anything_missing' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
