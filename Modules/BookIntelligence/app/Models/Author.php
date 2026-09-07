<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Author extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_authors';

    protected $fillable = ['team_id', 'user_id', 'name', 'bio', 'website'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
