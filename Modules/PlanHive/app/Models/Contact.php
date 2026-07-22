<?php

namespace Modules\PlanHive\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\PlanHive\Models\Concerns\BelongsToCurrentTeam;

class Contact extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'planhive_contacts';

    protected $fillable = [
        'team_id',
        'project_id',
        'user_id',
        'name',
        'email',
        'phone',
        'company',
        'job_title',
        'address',
        'notes',
        'tags',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTagsArrayAttribute(): array
    {
        return array_filter(array_map('trim', explode(',', (string) $this->tags)));
    }
}
