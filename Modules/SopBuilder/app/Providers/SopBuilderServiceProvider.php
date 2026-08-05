<?php

namespace Modules\SopBuilder\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SopBuilderServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'SopBuilder';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'sopbuilder';

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
