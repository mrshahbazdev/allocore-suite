<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class ProductType extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_product_types';

    protected $fillable = [
        'team_id', 'dentaltrack_company_id', 'name', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'dentaltrack_company_id');
    }

    public function processTemplates(): HasMany
    {
        return $this->hasMany(ProcessTemplate::class, 'dentaltrack_product_type_id')->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'dentaltrack_product_type_id');
    }
}
