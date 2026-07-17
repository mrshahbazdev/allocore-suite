<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageTranslation extends Model
{
    protected $fillable = [
        'page_id',
        'locale',
        'slug',
        'title',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'body',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
