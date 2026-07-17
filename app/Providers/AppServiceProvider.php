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
    }
}
