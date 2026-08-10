<?php

namespace Modules\ClusterForge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $table = 'clusterforge_questions';

    protected $fillable = [
        'subtopic_id',
        'question',
        'answer',
        'sort_order',
    ];

    public function subtopic(): BelongsTo
    {
        return $this->belongsTo(Subtopic::class);
    }
}
