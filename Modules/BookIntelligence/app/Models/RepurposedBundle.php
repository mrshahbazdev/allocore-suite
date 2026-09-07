<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class RepurposedBundle extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_repurposed_bundles';

    protected $fillable = [
        'team_id',
        'book_id',
        'user_id',
        'blog_articles',
        'linkedin_posts',
        'faq_articles',
        'checklists',
        'practical_guides',
        'whitepaper_concepts',
    ];

    protected $casts = [
        'blog_articles' => 'array',
        'linkedin_posts' => 'array',
        'faq_articles' => 'array',
        'checklists' => 'array',
        'practical_guides' => 'array',
        'whitepaper_concepts' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
