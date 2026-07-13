<?php

namespace Modules\LeadQuality\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class Contact extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_contacts';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'company',
        'position',
        'email',
        'website',
        'linkedin',
        'status',
        'industry',
        'role',
        'source',
        'budget',
        'budget_range',
        'employee_count_range',
        'priority',
        'last_interaction_at',
        'tags',
        'notes',
        'ai_high_probability',
        'pipeline_stage',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'last_interaction_at' => 'datetime',
            'budget' => 'decimal:2',
            'ai_high_probability' => 'boolean',
            'priority' => 'integer',
            'score' => 'integer',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sequences(): BelongsToMany
    {
        return $this->belongsToMany(Sequence::class, 'leadquality_contact_sequence')
            ->withPivot('current_step_id', 'next_run_at', 'status')
            ->withTimestamps();
    }
}
