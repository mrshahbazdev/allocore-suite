<?php

namespace Modules\ClusterForge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subtopic extends Model
{
    protected $table = 'clusterforge_subtopics';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'long_tail_keyword',
        'search_volume',
        'cpc',
        'competition',
        'competition_index',
        'low_bid',
        'high_bid',
        'sort_order',
        'cluster_title',
        'cluster_content',
        'cluster_meta_description',
    ];

    protected $casts = [
        'search_volume' => 'integer',
        'cpc' => 'float',
        'competition_index' => 'integer',
        'low_bid' => 'float',
        'high_bid' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }
}
