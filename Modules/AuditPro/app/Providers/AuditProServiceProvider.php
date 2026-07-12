<?php

namespace Modules\AuditPro\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\View;
use Modules\AuditPro\Services\BusinessReadinessSnapshot;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuditProServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'AuditPro';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auditpro';

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
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        app(DashboardWidgetRegistry::class)->register('audit', 'auditpro::dashboard-widget', 10);

        View::composer('auditpro::dashboard-widget', function ($view): void {
            $view->with(
                app(BusinessReadinessSnapshot::class)->forTeam(auth()->user()?->currentTeam)
            );
        });
    }
}
