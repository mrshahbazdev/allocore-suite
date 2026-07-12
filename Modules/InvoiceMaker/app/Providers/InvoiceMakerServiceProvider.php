<?php

namespace Modules\InvoiceMaker\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Modules\InvoiceMaker\Console\Commands\ProcessRecurringInvoices;
use Modules\InvoiceMaker\Console\Commands\UpdateOverdueInvoices;
use Modules\InvoiceMaker\Services\DashboardSnapshot;
use Nwidart\Modules\Support\ModuleServiceProvider;

class InvoiceMakerServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'InvoiceMaker';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'invoicemaker';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        ProcessRecurringInvoices::class,
        UpdateOverdueInvoices::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        app(DashboardWidgetRegistry::class)->register(
            'invoice-maker',
            'invoicemaker::dashboard-widget',
            20,
        );

        View::composer('invoicemaker::dashboard-widget', function ($view): void {
            $view->with(
                app(DashboardSnapshot::class)->forTeam(auth()->user()?->currentTeam)
            );
        });
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('invoicemaker:process-recurring')->dailyAt('00:10');
        $schedule->command('invoicemaker:update-overdue')->dailyAt('00:20');
    }
}
