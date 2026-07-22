
<?php

namespace Modules\VisionFlow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\VisionFlow\Models\Concerns\BelongsToCurrentTeam;

class Value extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'visionflow_values';

    protected $fillable = ['organization_id', 'title', 'description', 'status', 'sort_order', 'version', 'approved_at', 'approved_by'];

    protected $casts = ['approved_at' => 'datetime', 'version' => 'integer', 'sort_order' => 'integer'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function principles(): HasMany
    {
        return $this->hasMany(Principle::class);
    }

    public function goals(): BelongsToMany
    {
        return $this->belongsToMany(StrategicGoal::class, 'visionflow_goal_value');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
