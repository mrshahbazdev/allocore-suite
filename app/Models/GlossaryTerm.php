<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlossaryTerm extends Model
{
    protected $fillable = [
        'term',
        'slug',
        'definition',
        'simple_definition',
        'category',
        'related_modules',
        'is_published',
        'is_beginner_friendly',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'related_modules' => 'array',
            'is_published' => 'boolean',
            'is_beginner_friendly' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where(function ($q) use ($moduleKey) {
            $q->whereJsonContains('related_modules', $moduleKey)
                ->orWhereJsonContains('related_modules', [$moduleKey]);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
