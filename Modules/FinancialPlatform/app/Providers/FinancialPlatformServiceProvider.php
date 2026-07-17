<?php

namespace Modules\FinancialPlatform\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Modules\FinancialPlatform\Console\ProcessKpiReports;
use Modules\FinancialPlatform\Services\DashboardSnapshot;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FinancialPlatformServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'FinancialPlatform';

    protected string $nameLower = 'financialplatform';

    protected array $commands = [
        ProcessKpiReports::class,
    ];

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        app(DashboardWidgetRegistry::class)->register(
            'financial-platform',
            'financialplatform::dashboard-widget',
            40,
        );

        View::composer('financialplatform::dashboard-widget', function ($view): void {
            $view->with(
                app(DashboardSnapshot::class)->forTeam(auth()->user()?->currentTeam)
            );
        });
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('financial-platform:send-kpi-reports')->dailyAt('08:00');
    }
}
