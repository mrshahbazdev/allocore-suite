<?php

namespace Modules\LoopEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class StepOption extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_step_options';

    protected $fillable = [
        'team_id',
        'step_id',
        'label_en',
        'label_de',
        'value',
        'order',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ProcessStep::class, 'step_id');
    }

    public function transition(): HasOne
    {
        return $this->hasOne(StepTransition::class, 'option_id');
    }

    public function localizedLabel(): string
    {
        return app()->getLocale() === 'de' && $this->label_de ? $this->label_de : $this->label_en;
    }
}
