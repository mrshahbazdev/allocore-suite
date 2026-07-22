<?php

namespace Modules\FocusMatrix\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\FocusMatrix\Models\Concerns\BelongsToCurrentTeam;

class Task extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'focusmatrix_tasks';

    public const STATUS_INBOX = 'inbox';

    public const STATUS_KEEP = 'keep';

    public const STATUS_DELEGATE = 'delegate';

    public const STATUS_DROP = 'drop';

    public const STATUS_DONE = 'done';

    public const CATEGORIES = [
        'strategy' => 'Strategy & direction',
        'key_decisions' => 'Key decisions with scope',
        'key_people' => 'Select & develop key people',
        'responsibility' => 'Take responsibility',
    ];

    protected $fillable = [
        'team_id',
        'user_id',
        'title',
        'description',
        'status',
        'only_you_category',
        'source',
        'due_at',
        'focused_block_at',
        'ai_suggestion',
        'ai_confidence',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'focused_block_at' => 'datetime',
        'completed_at' => 'datetime',
        'ai_confidence' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function delegation(): HasOne
    {
        return $this->hasOne(Delegation::class);
    }
}
