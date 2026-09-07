<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class SearchQuery extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_search_queries';

    protected $fillable = [
        'team_id',
        'user_id',
        'query',
        'ai_answer',
        'matched_book_id',
        'matched_book_ids',
        'has_results',
        'is_resolved',
        'feedback',
    ];

    protected $casts = [
        'matched_book_ids' => 'array',
        'has_results' => 'boolean',
        'is_resolved' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchedBook(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'matched_book_id');
    }
}
