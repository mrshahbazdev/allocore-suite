<?php

namespace Modules\LoopEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\LoopEngine\Models\Concerns\BelongsToCurrentTeam;

class RunLog extends Model
{
    use BelongsToCurrentTeam, HasFactory;

    protected $table = 'loopengine_run_logs';

    protected $fillable = [
        'team_id',
        'run_id',
        'user_id',
        'action',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ProcessRun::class, 'run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
