<?php

namespace Modules\BookIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class Publisher extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'bookintelligence_publishers';

    protected $fillable = ['team_id', 'user_id', 'name', 'website'];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
