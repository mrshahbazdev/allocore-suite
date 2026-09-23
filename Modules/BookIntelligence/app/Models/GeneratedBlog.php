<?php

namespace Modules\BookIntelligence\Models;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class GeneratedBlog extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $table = 'bookintelligence_generated_blogs';

    protected $fillable = [
        'team_id',
        'user_id',
        'book_id',
        'opportunity_id',
        'post_id',
        'title',
        'slug',
        'target_keyword',
        'meta_description',
        'section_1_problem',
        'section_2_root_causes',
        'section_3_solutions',
        'section_4_implementation',
        'section_5_common_mistakes',
        'section_6_summary',
        'section_7_cta',
        'section_8_recommended_book',
        'full_html_content',
        'status',
    ];

    protected $casts = [
        'section_8_recommended_book' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(ContentOpportunity::class, 'opportunity_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
