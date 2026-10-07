<?php

namespace Modules\DentalTrack\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;
use Modules\DentalTrack\Models\Order;
use Modules\InvoiceMaker\Jobs\PushAllocoreMetric;

class PushOverdueOrders extends Command
{
    protected $signature = 'dentaltrack:push-overdue';

    protected $description = 'Push order.overdue signals to Allocore Manager for newly overdue orders';

    public function handle(): int
    {
        $pushed = 0;

        Order::withoutGlobalScopes()
            ->whereNotNull('due_date')
            ->whereNull('overdue_notified_at')
            ->whereDate('due_date', '<', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->chunkById(100, function ($orders) use (&$pushed): void {
                foreach ($orders as $order) {
                    PushAllocoreMetric::dispatch('order.overdue', [
                        'company_key' => Team::find($order->team_id)?->name,
                        'order_id' => $order->id,
                        'tracking_code' => $order->tracking_code,
                        'doctor_name' => $order->doctor_name,
                        'due_date' => $order->due_date?->toDateString(),
                    ]);

                    $order->update(['overdue_notified_at' => now()]);
                    $pushed++;
                }
            });

        $this->info("{$pushed} overdue order signals pushed.");

        return self::SUCCESS;
    }
}
