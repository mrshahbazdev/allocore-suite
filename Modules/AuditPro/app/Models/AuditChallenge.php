<?php

namespace Modules\AuditPro\Models;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditChallenge extends Model
{
    protected $table = 'auditpro_challenges';

    protected $fillable = [
        'team_id', 'user_id', 'small_audit_id', 'pillar', 'status',
        'steps', 'progress', 'started_at', 'completed_at', 'next_challenge_at',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'progress' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'next_challenge_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'small_audit_id');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true);
    }

    public function completionPercentage(): int
    {
        if (empty($this->steps)) {
            return 0;
        }

        $total = count($this->steps);
        $done = collect($this->steps)->where('completed', true)->count();

        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }
}
