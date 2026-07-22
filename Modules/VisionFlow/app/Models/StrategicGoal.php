<?php

namespace Modules\VisionFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StrategicGoal extends Model
{
    use HasFactory;

    protected $table = 'visionflow_strategic_goals';

    protected $fillable = ['organization_id', 'title', 'description', 'category', 'time_horizon', 'status'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(Value::class, 'visionflow_goal_value');
    }

    public function principles(): BelongsToMany
    {
        return $this->belongsToMany(Principle::class, 'visionflow_goal_principle');
    }
}
