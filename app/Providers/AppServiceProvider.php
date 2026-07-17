<?php

namespace App\Providers;

use App\Mail\Transport\DynamicSmtpTransport;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DashboardWidgetRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app('mail.manager')->extend(DynamicSmtpTransport::class, fn () => new DynamicSmtpTransport);

        $this->registerDashboardWidgets();
    }

    protected function registerDashboardWidgets(): void
    {
        $registry = app(DashboardWidgetRegistry::class);

        $registry->register('audit', 'dashboard.widgets.audit', 10);
        $registry->register('invoice-maker', 'dashboard.widgets.invoice-maker', 20);
        $registry->register('lead-quality', 'dashboard.widgets.lead-quality', 30);
        $registry->register('financial-platform', 'dashboard.widgets.financial-platform', 40);
    }
}
