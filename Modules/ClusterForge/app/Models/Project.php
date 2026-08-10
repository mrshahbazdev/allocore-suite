<?php

namespace Modules\ClusterForge\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\ClusterForge\Models\Concerns\BelongsToCurrentTeam;

class Project extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'clusterforge_projects';

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATING_SUBTOPICS = 'generating_subtopics';

    public const STATUS_GENERATING_QUESTIONS = 'generating_questions';

    public const STATUS_GENERATING_ANSWERS = 'generating_answers';

    public const STATUS_GENERATING_PAGES = 'generating_pages';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'team_id',
        'user_id',
        'topic',
        'website',
        'language',
        'status',
        'error',
        'pillar_title',
        'pillar_content',
        'pillar_meta_description',
    ];

    protected $casts = [
        'pillar_content' => 'string',
    ];

    public function languageName(): string
    {
        return match ($this->language) {
            'de' => 'German',
            default => 'English',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class)->orderBy('sort_order');
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, Subtopic::class);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_GENERATING_SUBTOPICS)
            ->orWhere('status', self::STATUS_GENERATING_QUESTIONS)
            ->orWhere('status', self::STATUS_GENERATING_ANSWERS)
            ->orWhere('status', self::STATUS_GENERATING_PAGES);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_GENERATING_SUBTOPICS,
            self::STATUS_GENERATING_QUESTIONS,
            self::STATUS_GENERATING_ANSWERS,
            self::STATUS_GENERATING_PAGES,
        ], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Queued'),
            self::STATUS_GENERATING_SUBTOPICS => __('Generating subtopics'),
            self::STATUS_GENERATING_QUESTIONS => __('Generating questions'),
            self::STATUS_GENERATING_ANSWERS => __('Generating answers'),
            self::STATUS_GENERATING_PAGES => __('Writing pages'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_FAILED => __('Failed'),
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function progressPercent(): int
    {
        return match ($this->status) {
            self::STATUS_PENDING => 5,
            self::STATUS_GENERATING_SUBTOPICS => 20,
            self::STATUS_GENERATING_QUESTIONS => 40,
            self::STATUS_GENERATING_ANSWERS => 70,
            self::STATUS_GENERATING_PAGES => 90,
            self::STATUS_COMPLETED => 100,
            self::STATUS_FAILED => 100,
            default => 0,
        };
    }
}
