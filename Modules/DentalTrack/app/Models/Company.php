<?php

namespace Modules\DentalTrack\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class Company extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_companies';

    protected $fillable = [
        'team_id', 'name', 'slug', 'address', 'settings', 'logo', 'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $company): void {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    public function labs(): HasMany
    {
        return $this->hasMany(Lab::class, 'dentaltrack_company_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'dentaltrack_company_id');
    }

    public function productTypes(): HasMany
    {
        return $this->hasMany(ProductType::class, 'dentaltrack_company_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'dentaltrack_company_id');
    }
}
