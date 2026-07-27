<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'slug',
        'definition',
        'category',
        'related_modules',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'related_modules' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
