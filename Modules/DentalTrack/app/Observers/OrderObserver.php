<?php

namespace Modules\DentalTrack\Observers;

use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Services\PredictionService;

class OrderObserver
{
    public function __construct(
        private readonly PredictionService $predictionService,
    ) {}

    public function created(Order $order): void
    {
        if ($order->steps()->count() > 0) {
            $this->predictionService->predictCompletion($order);
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === OrderStatus::Completed) {
            $this->predictionService->updateAccuracy($order);
        }
    }
}
