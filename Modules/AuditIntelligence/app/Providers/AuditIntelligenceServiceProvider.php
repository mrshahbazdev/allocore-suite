<?php

namespace Modules\AuditIntelligence\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\AuditIntelligence\Models\Recommendation;
use Modules\AuditIntelligence\Services\ManagerReporter;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuditIntelligenceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'AuditIntelligence';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auditintelligence';

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

    public function boot(): void
    {
        Recommendation::saved(
            fn (Recommendation $recommendation) => app(ManagerReporter::class)->suggestion($recommendation)
        );
    }

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
