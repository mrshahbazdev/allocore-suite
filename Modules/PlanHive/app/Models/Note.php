<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Note extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_notes';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'title',
        'content',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
