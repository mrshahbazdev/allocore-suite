<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class Lab extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_labs';

    protected $fillable = [
        'team_id', 'dentaltrack_company_id', 'name', 'location', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'dentaltrack_company_id');
    }

    public function workstations(): HasMany
    {
        return $this->hasMany(Workstation::class, 'dentaltrack_lab_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'dentaltrack_lab_id');
    }
}
