<?php

namespace Modules\BunnyBand\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BunnyBandServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'BunnyBand';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'bunnyband';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
