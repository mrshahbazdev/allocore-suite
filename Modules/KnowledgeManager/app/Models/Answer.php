<?php

namespace Modules\KnowledgeManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\KnowledgeManager\Models\Concerns\BelongsToCurrentTeam;

class Answer extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'knowledge_answers';

    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
