<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    protected $fillable = [
        'name', 'rate', 'country', 'region', 'is_default', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function ($taxRate) {
            if ($taxRate->is_default) {
                $query = static::query();
                if ($taxRate->getKey()) {
                    $query->whereKeyNot($taxRate->getKey());
                }
                $query->update(['is_default' => false]);
            }
        });
    }

    public static function forCountry(?string $country): ?self
    {
        if ($country) {
            $rate = static::where('country', $country)->where('is_active', true)->first();
            if ($rate) {
                return $rate;
            }
        }

        return static::where('is_default', true)->where('is_active', true)->first();
    }
}
