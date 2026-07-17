<?php

namespace Modules\ClusterForge\Models;

use App\Models\Team;
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
        'keywords',
        'clusters',
        'is_public',
        'public_slug',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'clusters' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
