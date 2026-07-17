<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['slug', 'is_published', 'sort_order'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class);
    }

    public function translation(?string $locale = null): ?PageTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations()->where('locale', $locale)->first();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale ??= app()->getLocale();

        return self::whereHas('translations', function ($query) use ($slug, $locale) {
            $query->where('slug', $slug)->where('locale', $locale);
        })->first();
    }
}
