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
}
