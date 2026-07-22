
<?php

namespace Modules\VisionFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\VisionFlow\Models\Concerns\BelongsToCurrentTeam;

class Principle extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'visionflow_principles';

    protected $fillable = ['organization_id', 'value_id', 'statement', 'status', 'alignment_score'];

    protected $casts = ['alignment_score' => 'decimal:2'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class);
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(StrategicGoal::class, 'visionflow_goal_principle');
    }
}
