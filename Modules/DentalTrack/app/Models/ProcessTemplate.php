<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class ProcessTemplate extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_process_templates';

    protected $fillable = [
        'team_id', 'dentaltrack_product_type_id', 'sort_order', 'step_name', 'expected_minutes',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'expected_minutes' => 'integer',
    ];

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'dentaltrack_product_type_id');
    }
}
