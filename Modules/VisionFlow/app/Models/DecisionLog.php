<?php

namespace Modules\VisionFlow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DecisionLog extends Model
{
    use HasFactory;

    protected $table = 'visionflow_decision_logs';

    protected $fillable = ['organization_id', 'user_id', 'title', 'description', 'decision', 'supporting_value_id', 'supporting_mission_id'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class, 'supporting_value_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'supporting_mission_id');
    }
}
