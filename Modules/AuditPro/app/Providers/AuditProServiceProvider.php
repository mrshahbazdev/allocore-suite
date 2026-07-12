<?php

namespace Modules\AuditPro\Providers;

use App\Support\DashboardWidgetRegistry;
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
    }
}
