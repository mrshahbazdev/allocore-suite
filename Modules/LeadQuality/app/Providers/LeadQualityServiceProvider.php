<?php

namespace Modules\LeadQuality\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Modules\LeadQuality\Console\Commands\ProcessDripCampaigns;
use Modules\LeadQuality\Services\DashboardSnapshot;
use Nwidart\Modules\Support\ModuleServiceProvider;

class LeadQualityServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'LeadQuality';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'leadquality';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        ProcessDripCampaigns::class,
    ];

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
        parent::boot();

        app(DashboardWidgetRegistry::class)->register(
            'lead-quality',
            'leadquality::dashboard-widget',
            30,
        );

        View::composer('leadquality::dashboard-widget', function ($view): void {
            $view->with(
                app(DashboardSnapshot::class)->forTeam(auth()->user()?->currentTeam)
            );
        });
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('leadquality:process-drip-campaigns')->hourly();
    }
}
