<?php

namespace Modules\VisionFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $table = 'visionflow_projects';

    protected $fillable = ['organization_id', 'mission_id', 'name', 'description', 'status'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }
}
