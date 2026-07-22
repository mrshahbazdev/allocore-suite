<?php

namespace Modules\DentalTrack\Providers;

use Modules\DentalTrack\Console\Commands\GeneratePredictions;
use Modules\DentalTrack\Models\Order;
use Modules\DentalTrack\Observers\OrderObserver;
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
    }
}
