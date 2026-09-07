<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class KnowledgeGap extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_OPEN = 'open';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    protected $table = 'bookintelligence_knowledge_gaps';

    protected $fillable = [
        'team_id',
        'user_id',
        'query',
        'status',
        'search_count',
        'suggested_books',
        'admin_notes',
    ];

    protected $casts = [
        'search_count' => 'integer',
        'suggested_books' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_REVIEWING]);
    }
}
