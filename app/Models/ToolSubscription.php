<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ToolSubscription extends Model
{
    protected $fillable = [
        'billable_type', 'billable_id', 'plan_id', 'payment_method', 'billing_interval',
        'status', 'gateway_reference', 'receipt_path', 'admin_note', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function activate(): void
    {
        $interval = $this->billing_interval === 'yearly' ? now()->addYear() : now()->addMonth();
        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $interval,
        ]);
    }
}
