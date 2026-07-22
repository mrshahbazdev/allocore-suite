<?php

namespace Modules\NurDu\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class NurDuServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'NurDu';

    protected string $nameLower = 'nurdu';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
        $this->loadMigrationsFrom(module_path('NurDu', 'database/migrations'));
        $this->loadViewsFrom(module_path('NurDu', 'resources/views'), 'nurdu');
    }
}
