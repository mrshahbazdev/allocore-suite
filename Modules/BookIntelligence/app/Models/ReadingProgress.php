<?php

namespace Modules\BookIntelligence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\BookIntelligence\Models\Concerns\BelongsToCurrentTeam;

class ReadingProgress extends Model
{
    use BelongsToCurrentTeam;

    public const STATUSES = ['planned', 'reading', 'read'];

    protected $table = 'bookintelligence_reading_progress';

    protected $fillable = [
        'team_id',
        'book_id',
        'user_id',
        'status',
        'progress_percent',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'progress_percent' => 'integer',
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
