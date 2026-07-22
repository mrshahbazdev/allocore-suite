<?php

namespace Modules\DentalTrack\Services;

use Illuminate\Support\Carbon;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Enums\ScanEventType;
use Modules\DentalTrack\Enums\StepStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ScanEvent;
use Modules\DentalTrack\Models\Workstation;

class ScanService
{
    public function startWork(Order $order, Workstation $workstation, $user, ?string $notes = null): ScanEvent
    {
        $this->validateNotActiveElsewhere($order, $workstation);

        $currentStep = $order->currentStep();

        if ($currentStep !== null) {
            $currentStep->update([
                'status' => StepStatus::InProgress,
                'assigned_to' => $user->id,
            ]);
        }

        if ($order->status === OrderStatus::Pending) {
            $order->update(['status' => OrderStatus::InProgress]);
        }

        return ScanEvent::create([
            'dentaltrack_order_id' => $order->id,
            'dentaltrack_order_step_id' => $currentStep?->id,
            'dentaltrack_workstation_id' => $workstation->id,
            'user_id' => $user->id,
            'event_type' => ScanEventType::Start,
            'scanned_at' => Carbon::now(),
            'notes' => $notes,
        ]);
    }

    public function completeWork(Order $order, Workstation $workstation, $user, ?string $notes = null): ScanEvent
    {
        $currentStep = $order->currentStep();
        $duration = $this->calculateDuration($order, $workstation);

        $event = ScanEvent::create([
            'dentaltrack_order_id' => $order->id,
            'dentaltrack_order_step_id' => $currentStep?->id,
            'dentaltrack_workstation_id' => $workstation->id,
            'user_id' => $user->id,
            'event_type' => ScanEventType::Complete,
            'scanned_at' => Carbon::now(),
            'duration_seconds' => $duration,
            'notes' => $notes,
        ]);

        if ($currentStep !== null) {
            $currentStep->update(['status' => StepStatus::Done]);
        }

        $this->checkOrderCompletion($order);

        return $event;
    }

    public function pauseWork(Order $order, Workstation $workstation, $user, ?string $notes = null): ScanEvent
    {
        $currentStep = $order->currentStep();
        $duration = $this->calculateDuration($order, $workstation);

        return ScanEvent::create([
            'dentaltrack_order_id' => $order->id,
            'dentaltrack_order_step_id' => $currentStep?->id,
            'dentaltrack_workstation_id' => $workstation->id,
            'user_id' => $user->id,
            'event_type' => ScanEventType::Pause,
            'scanned_at' => Carbon::now(),
            'duration_seconds' => $duration,
            'notes' => $notes,
        ]);
    }

    public function transferToWaiting(Order $order, Workstation $waitingArea, $user, ?string $notes = null): ScanEvent
    {
        $currentStep = $order->currentStep();

        return ScanEvent::create([
            'dentaltrack_order_id' => $order->id,
            'dentaltrack_order_step_id' => $currentStep?->id,
            'dentaltrack_workstation_id' => $waitingArea->id,
            'user_id' => $user->id,
            'event_type' => ScanEventType::TransferToWaiting,
            'scanned_at' => Carbon::now(),
            'notes' => $notes,
        ]);
    }

    private function validateNotActiveElsewhere(Order $order, Workstation $targetWorkstation): void
    {
        $lastEvent = $order->scanEvents()
            ->where('event_type', ScanEventType::Start)
            ->first();

        if ($lastEvent === null) {
            return;
        }

        $hasBeenCompleted = $order->scanEvents()
            ->where('scanned_at', '>', $lastEvent->scanned_at)
            ->whereIn('event_type', [ScanEventType::Complete, ScanEventType::Pause, ScanEventType::TransferToWaiting])
            ->exists();

        if (! $hasBeenCompleted && $lastEvent->dentaltrack_workstation_id !== $targetWorkstation->id) {
            $stationName = $lastEvent->workstation?->name ?? 'unknown';
            throw new \RuntimeException(
                "Order #{$order->id} is currently active at workstation '{$stationName}'. Complete or pause it there first."
            );
        }
    }

    private function calculateDuration(Order $order, Workstation $workstation): ?int
    {
        $lastStart = $order->scanEvents()
            ->where('dentaltrack_workstation_id', $workstation->id)
            ->where('event_type', ScanEventType::Start)
            ->orderByDesc('scanned_at')
            ->first();

        if ($lastStart === null) {
            return null;
        }

        return max(0, (int) abs(Carbon::now()->diffInSeconds($lastStart->scanned_at)));
    }

    private function checkOrderCompletion(Order $order): void
    {
        $allDone = $order->steps()
            ->whereNotIn('status', [StepStatus::Done, StepStatus::Skipped])
            ->doesntExist();

        if ($allDone) {
            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => Carbon::now(),
            ]);
        }
    }
}
