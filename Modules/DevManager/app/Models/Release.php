<?php

namespace Modules\DevManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DevManager\Models\Concerns\BelongsToCurrentTeam;

class Release extends Model
{
    use BelongsToCurrentTeam;
    use HasFactory;

    protected $table = 'devmanager_releases';

    protected $guarded = [];

    protected $casts = [
        'released_at' => 'date',
        'metadata' => 'array',
    ];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
