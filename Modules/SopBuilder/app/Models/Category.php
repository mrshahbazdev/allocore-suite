<?php

namespace Modules\SopBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\SopBuilder\Models\Concerns\BelongsToCurrentTeam;

class Category extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'sop_categories';

    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function sops(): HasMany
    {
        return $this->hasMany(Sop::class, 'category_id');
    }
}
