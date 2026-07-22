<?php

namespace Modules\VisionFlow\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'visionflow_missions';

    protected $fillable = ['organization_id', 'vision_id', 'title', 'description', 'owner_id', 'status', 'review_cadence', 'next_review_at'];

    protected $casts = ['next_review_at' => 'datetime'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function vision(): BelongsTo
    {
        return $this->belongsTo(Vision::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
