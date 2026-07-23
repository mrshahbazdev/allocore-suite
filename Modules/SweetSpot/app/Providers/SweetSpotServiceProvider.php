<?php

namespace Modules\SweetSpot\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\SweetSpot\Console\Commands\RecalculateScores;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SweetSpotServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'SweetSpot';

    protected string $nameLower = 'sweet-spot';

    protected array $commands = [
        RecalculateScores::class,
    ];

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(module_path('SweetSpot', 'database/migrations'));
        $this->loadViewsFrom(module_path('SweetSpot', 'resources/views'), 'sweetspot');
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('sweet-spot:recalculate')->dailyAt('02:00');
    }
}
