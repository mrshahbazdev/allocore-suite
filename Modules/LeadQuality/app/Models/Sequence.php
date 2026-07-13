<?php

namespace Modules\LeadQuality\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LeadQuality\Models\Concerns\BelongsToCurrentTeam;

class Sequence extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'leadquality_sequences';

    protected $fillable = [
        'team_id',
        'user_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(SequenceStep::class)->orderBy('order');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'leadquality_contact_sequence')
            ->withPivot('current_step_id', 'next_run_at', 'status')
            ->withTimestamps();
    }
}
