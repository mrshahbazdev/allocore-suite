<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class BookAnalysis extends Model
{
    use BelongsToCurrentTeam;

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'bookintelligence_book_analyses';

    protected $fillable = [
        'team_id',
        'book_id',
        'user_id',
        'status',
        'source_material',
        'source_fingerprint',
        'short_summary',
        'long_summary',
        'key_takeaways',
        'frameworks',
        'actionable_recommendations',
        'provider',
        'generated_at',
        'error',
    ];

    protected $casts = [
        'key_takeaways' => 'array',
        'frameworks' => 'array',
        'actionable_recommendations' => 'array',
        'generated_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_GENERATING,
        ], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOutdated(): bool
    {
        return $this->isCompleted()
            && $this->source_fingerprint !== $this->book->analysisFingerprint($this->source_material);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Queued'),
            self::STATUS_GENERATING => __('Analyzing book'),
            self::STATUS_COMPLETED => __('Ready'),
            self::STATUS_FAILED => __('Analysis failed'),
            default => ucfirst((string) $this->status),
        };
    }
}
