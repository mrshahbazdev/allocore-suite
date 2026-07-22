<?php

namespace Modules\LoopEngine\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class Process extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_processes';

    protected $fillable = [
        'team_id',
        'user_id',
        'name_en',
        'name_de',
        'description_en',
        'description_de',
        'status',
        'version',
        'category',
        'icon',
        'parent_id',
        'is_latest_version',
    ];

    protected function casts(): array
    {
        return [
            'is_latest_version' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ProcessStep::class, 'process_id')->orderBy('order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ProcessRun::class, 'process_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeamAssignment::class, 'process_id');
    }

    public function template(): HasMany
    {
        return $this->hasMany(ProcessTemplate::class, 'process_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function firstStep(): ?ProcessStep
    {
        return $this->steps()->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'de' && $this->name_de ? $this->name_de : $this->name_en;
    }

    public function localizedDescription(): ?string
    {
        return app()->getLocale() === 'de' && $this->description_de ? $this->description_de : $this->description_en;
    }

    public function duplicate(User $user, ?int $teamId = null): self
    {
        $clone = $this->replicate(['status', 'version', 'parent_id', 'is_latest_version']);
        $clone->status = 'draft';
        $clone->version = 1;
        $clone->parent_id = null;
        $clone->is_latest_version = true;
        $clone->user_id = $user->id;
        $clone->team_id = $teamId ?? $user->current_team_id;
        $clone->name_en = $this->name_en.' (Copy)';
        $clone->name_de = $this->name_de ? $this->name_de.' (Kopie)' : null;
        $clone->save();

        $stepMap = [];
        $origSteps = $this->steps()->with('options', 'transitions')->get();

        foreach ($origSteps as $origStep) {
            $newStep = $origStep->replicate();
            $newStep->process_id = $clone->id;
            $newStep->save();
            $stepMap[$origStep->id] = $newStep->id;

            $optionMap = [];
            foreach ($origStep->options as $option) {
                $newOption = $option->replicate();
                $newOption->step_id = $newStep->id;
                $newOption->save();
                $optionMap[$option->id] = $newOption->id;
            }

            foreach ($origStep->transitions as $transition) {
                $newStep->transitions()->create([
                    'option_id' => $transition->option_id ? ($optionMap[$transition->option_id] ?? null) : null,
                    'action_type' => $transition->action_type,
                    'target_step_id' => $transition->target_step_id ? ($stepMap[$transition->target_step_id] ?? null) : null,
                    'target_process_id' => $transition->target_process_id,
                ]);
            }
        }

        return $clone->fresh();
    }

    public function createNewVersion(User $user): self
    {
        $parentId = $this->parent_id ?? $this->id;

        $this->update(['is_latest_version' => false]);

        $newVersion = $this->duplicate($user);
        $newVersion->update([
            'parent_id' => $parentId,
            'version' => $this->version + 1,
            'is_latest_version' => true,
            'name_en' => $this->name_en,
            'name_de' => $this->name_de,
        ]);

        return $newVersion->fresh();
    }
}
