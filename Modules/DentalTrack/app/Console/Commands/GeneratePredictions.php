<?php

namespace Modules\DentalTrack\Console\Commands;

use Illuminate\Console\Command;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Services\PredictionService;

class GeneratePredictions extends Command
{
    protected $signature = 'dentaltrack:predictions:generate {--update-accuracy : Also update accuracy for completed orders}';

    protected $description = 'Generate completion predictions for active orders and optionally update accuracy';

    public function handle(PredictionService $service): int
    {
        $activeOrders = Order::whereIn('status', [OrderStatus::InProgress, OrderStatus::Pending])->get();

        $generated = 0;
        foreach ($activeOrders as $order) {
            $result = $service->predictCompletion($order);
            if ($result !== null) {
                $generated++;
                $this->line("Order #{$order->id}: {$result['prediction']->predicted_minutes} min ({$result['basis']})");
            }
        }

        $this->info("Generated {$generated} predictions for {$activeOrders->count()} active orders.");

        if ($this->option('update-accuracy')) {
            $completedOrders = Order::where('status', OrderStatus::Completed)
                ->whereNotNull('completed_at')
                ->whereHas('predictions', fn ($q) => $q->whereNull('actual_minutes'))
                ->get();

            $updated = 0;
            foreach ($completedOrders as $order) {
                $service->updateAccuracy($order);
                $updated++;
            }

            $this->info("Updated accuracy for {$updated} completed orders.");
        }

        return self::SUCCESS;
    }
}
