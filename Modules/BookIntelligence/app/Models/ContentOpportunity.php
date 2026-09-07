<?php

namespace Modules\BookIntelligence\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class ContentOpportunity extends Model
{
    use BelongsToCurrentTeam;

    public const TYPE_BLOG = 'blog';
    public const TYPE_FAQ = 'faq';
    public const TYPE_WHITEPAPER = 'whitepaper';
    public const TYPE_NEWSLETTER = 'newsletter';
    public const TYPE_LINKEDIN = 'linkedin';

    public const STATUS_IDEA = 'idea';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_GENERATED = 'generated';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'bookintelligence_content_opportunities';

    protected $fillable = [
        'team_id',
        'user_id',
        'book_id',
        'title',
        'target_keyword',
        'content_type',
        'search_intent',
        'estimated_demand',
        'target_audience',
        'angle_hook',
        'status',
        'generated_post_id',
    ];

    protected $casts = [
        'target_audience' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'generated_post_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_ARCHIVED]);
    }
}
