<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Industry extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'locale',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function scopeClusters($query)
    {
        return $query->whereNull('parent_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSubIndustries($query)
    {
        return $query->whereNotNull('parent_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
