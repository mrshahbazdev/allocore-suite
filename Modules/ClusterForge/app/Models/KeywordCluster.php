<?php

namespace Modules\ClusterForge\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ClusterForge\Models\Concerns\BelongsToCurrentTeam;

class KeywordCluster extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'clusterforge_keyword_clusters';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'description',
        'tags',
        'keywords',
        'clusters',
        'algorithm',
        'status',
        'processing_error',
        'is_public',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'clusters' => 'array',
            'tags' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function shareUrl(): string
    {
        return route('clusterforge.public.show', $this->public_slug);
    }
}
