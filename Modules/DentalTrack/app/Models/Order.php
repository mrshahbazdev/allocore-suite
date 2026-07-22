<?php

namespace Modules\DentalTrack\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\DentalTrack\Enums\OrderPriority;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Enums\StepStatus;
use Modules\DentalTrack\Models\Concerns\BelongsToCurrentTeam;

class Order extends Model
{
    use BelongsToCurrentTeam;

    protected $table = 'dentaltrack_orders';

    protected $fillable = [
        'team_id', 'dentaltrack_company_id', 'dentaltrack_lab_id', 'dentaltrack_product_type_id',
        'patient_ref', 'doctor_name', 'qr_code', 'tracking_code', 'priority', 'due_date', 'status',
        'notes', 'predicted_completion_at', 'completed_at',
    ];

    protected $casts = [
        'priority' => OrderPriority::class,
        'status' => OrderStatus::class,
        'due_date' => 'date',
        'predicted_completion_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order): void {
            if (empty($order->qr_code)) {
                $order->qr_code = 'ORD-'.Str::ulid();
            }
            if (empty($order->tracking_code)) {
                $order->tracking_code = strtoupper(Str::random(8));
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'dentaltrack_company_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'dentaltrack_lab_id');
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'dentaltrack_product_type_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OrderStep::class, 'dentaltrack_order_id')->orderBy('sort_order');
    }

    public function scanEvents(): HasMany
    {
        return $this->hasMany(ScanEvent::class, 'dentaltrack_order_id')->orderByDesc('scanned_at');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class, 'dentaltrack_order_id');
    }

    public function reworkEvents(): HasMany
    {
        return $this->hasMany(ReworkEvent::class, 'dentaltrack_order_id');
    }

    public function currentStep(): ?OrderStep
    {
        return $this->steps()
            ->whereIn('status', [StepStatus::Pending, StepStatus::InProgress])
            ->orderBy('sort_order')
            ->first();
    }

    public function latestScanEvent(): ?ScanEvent
    {
        return $this->scanEvents()->first();
    }

    public function qrUrl(): string
    {
        return url('/dentaltrack/scan/'.$this->qr_code);
    }

    public function trackUrl(): string
    {
        return url('/dentaltrack/track?code='.$this->tracking_code);
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, [OrderStatus::Completed, OrderStatus::Cancelled], true);
    }

    public function completedStepsCount(): int
    {
        return $this->steps()->where('status', StepStatus::Done)->count();
    }

    public function totalStepsCount(): int
    {
        return $this->steps()->count();
    }

    public function progressPercentage(): float
    {
        $total = $this->totalStepsCount();
        if ($total === 0) {
            return 0;
        }

        return round(($this->completedStepsCount() / $total) * 100, 1);
    }
}
