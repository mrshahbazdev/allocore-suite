<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Topic extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_topics';

    protected $fillable = ['team_id', 'user_id', 'parent_id', 'name', 'slug', 'description'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Topic::class, 'parent_id')->orderBy('name');
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(
            Book::class,
            'bookintelligence_book_topic',
            'topic_id',
            'book_id',
        );
    }

    public function mainBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'main_topic_id');
    }
}
