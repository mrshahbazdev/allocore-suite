
<?php

namespace Modules\VisionFlow\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\VisionFlow\Models\Concerns\BelongsToCurrentTeam;

class Organization extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'visionflow_organizations';

    protected $fillable = ['team_id', 'user_id', 'name', 'slug', 'description', 'logo_url'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    public function principles(): HasMany
    {
        return $this->hasMany(Principle::class);
    }

    public function strategicGoals(): HasMany
    {
        return $this->hasMany(StrategicGoal::class);
    }

    public function visions(): HasMany
    {
        return $this->hasMany(Vision::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function decisionLogs(): HasMany
    {
        return $this->hasMany(DecisionLog::class);
    }
}
