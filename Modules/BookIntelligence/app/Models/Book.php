<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Book extends Model
{
    use BelongsToCurrentTeam;

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced', 'expert'];

    protected $table = 'bookintelligence_books';

    protected $fillable = [
        'team_id',
        'user_id',
        'author_id',
        'publisher_id',
        'main_topic_id',
        'title',
        'slug',
        'isbn',
        'publication_year',
        'page_count',
        'language',
        'difficulty',
        'cover_url',
        'description',
        'relevant_roles',
        'affiliate_link',
        'status',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'page_count' => 'integer',
        'relevant_roles' => 'array',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    public function mainTopic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'main_topic_id');
    }

    public function subtopics(): BelongsToMany
    {
        return $this->belongsToMany(
            Topic::class,
            'bookintelligence_book_topic',
            'book_id',
            'topic_id',
        );
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    public function currentUserProgress(): HasOne
    {
        return $this->hasOne(ReadingProgress::class)
            ->where('user_id', auth()->id());
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(BookAnalysis::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term): void {
            $builder
                ->where('title', 'like', '%'.$term.'%')
                ->orWhere('isbn', 'like', '%'.$term.'%')
                ->orWhere('description', 'like', '%'.$term.'%')
                ->orWhereHas('author', fn (Builder $author) => $author->where('name', 'like', '%'.$term.'%'));
        });
    }

    public function readingStatus(): string
    {
        return $this->currentUserProgress?->status ?? 'unassigned';
    }

    public function analysisFingerprint(?string $sourceMaterial = null): string
    {
        $this->loadMissing(['author', 'publisher', 'mainTopic', 'subtopics']);

        return hash('sha256', json_encode([
            'title' => $this->title,
            'author' => $this->author?->name,
            'publisher' => $this->publisher?->name,
            'publication_year' => $this->publication_year,
            'language' => $this->language,
            'difficulty' => $this->difficulty,
            'description' => $this->description,
            'main_topic' => $this->mainTopic?->name,
            'subtopics' => $this->subtopics->pluck('name')->sort()->values()->all(),
            'relevant_roles' => collect($this->relevant_roles)->sort()->values()->all(),
            'source_material' => trim((string) $sourceMaterial),
        ], JSON_THROW_ON_ERROR));
    }
}
