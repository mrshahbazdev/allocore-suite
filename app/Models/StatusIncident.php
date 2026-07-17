<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusIncident extends Model
{
    protected $fillable = [
        'title',
        'description',
        'severity',
        'status',
        'started_at',
        'resolved_at',
        'is_resolved',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'is_resolved' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_resolved', true);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('started_at')->take(10);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }
}
