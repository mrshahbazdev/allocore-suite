<?php

namespace Modules\BookIntelligence\Models;

use App\Models\GlossaryTerm;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class QuestionMapping extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_question_mappings';

    protected $fillable = [
        'team_id',
        'book_id',
        'user_id',
        'question',
        'answer_excerpt',
        'problem_statement',
        'target_audience',
        'when_to_read_trigger',
        'category',
        'priority',
        'audit_question_id',
        'module_key',
        'tool_explanation',
        'glossary_term_id',
        'is_active',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditQuestion(): BelongsTo
    {
        return $this->belongsTo(AuditQuestion::class, 'audit_question_id');
    }

    public function glossaryTerm(): BelongsTo
    {
        return $this->belongsTo(GlossaryTerm::class, 'glossary_term_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $nested) use ($term): void {
            $nested->where('question', 'like', "%{$term}%")
                ->orWhere('answer_excerpt', 'like', "%{$term}%")
                ->orWhere('problem_statement', 'like', "%{$term}%")
                ->orWhere('when_to_read_trigger', 'like', "%{$term}%")
                ->orWhereHas('book', function (Builder $b) use ($term): void {
                    $b->where('title', 'like', "%{$term}%");
                });
        });
    }
}
