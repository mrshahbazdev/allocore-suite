
<?php

namespace Modules\VisionFlow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\VisionFlow\Models\Concerns\BelongsToCurrentTeam;

class Vision extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'visionflow_visions';

    protected $fillable = ['organization_id', 'content', 'status', 'version', 'approved_at', 'approved_by', 'is_current'];

    protected $casts = ['approved_at' => 'datetime', 'is_current' => 'boolean', 'version' => 'integer'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
