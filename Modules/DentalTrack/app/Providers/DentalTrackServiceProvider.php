<?php

namespace Modules\DentalTrack\Providers;

use Modules\DentalTrack\Console\Commands\GeneratePredictions;
use Modules\DentalTrack\Enums\OrderStatus;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Models\ReworkEvent;
use Modules\DentalTrack\Observers\OrderObserver;
use Modules\InvoiceMaker\Jobs\PushAllocoreMetric;
use Nwidart\Modules\Support\ModuleServiceProvider;

class DentalTrackServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'DentalTrack';

    protected string $nameLower = 'dentaltrack';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->commands([GeneratePredictions::class]);

        Order::observe(OrderObserver::class);

        $this->loadMigrationsFrom(module_path('DentalTrack', 'database/migrations'));
        $this->loadViewsFrom(module_path('DentalTrack', 'resources/views'), 'dentaltrack');

        $this->registerAllocoreMetricPush();
    }

    private function registerAllocoreMetricPush(): void
    {
        Order::created(static function (Order $order): void {
            PushAllocoreMetric::dispatch('order_created', [
                'order_id' => (string) $order->id,
                'due_date' => $order->due_date?->toDateString(),
            ]);
        });

        Order::updated(static function (Order $order): void {
            if (! $order->wasChanged('status') || $order->status !== OrderStatus::Completed) {
                return;
            }

            PushAllocoreMetric::dispatch('order_done', [
                'order_id' => (string) $order->id,
            ]);

            if ($order->due_date && $order->completed_at
                && $order->completed_at->copy()->endOfDay()->gte($order->due_date)) {
                PushAllocoreMetric::dispatch('order_done_on_time', [
                    'order_id' => (string) $order->id,
                ]);
            }

            if ($order->completed_at) {
                PushAllocoreMetric::dispatch('order_lead_time', [
                    'order_id' => (string) $order->id,
                    'days' => round((float) $order->created_at->diffInDays($order->completed_at), 2),
                ]);
            }
        });

        ReworkEvent::created(static function (ReworkEvent $event): void {
            PushAllocoreMetric::dispatch('order_complaint', [
                'order_id' => (string) $event->dentaltrack_order_id,
                'cause' => $event->cause?->value,
            ]);
        });
    }
}
