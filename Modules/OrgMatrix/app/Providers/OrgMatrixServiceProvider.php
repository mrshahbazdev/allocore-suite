<?php

namespace Modules\OrgMatrix\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class OrgMatrixServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'OrgMatrix';

    protected string $nameLower = 'orgmatrix';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
        $this->loadMigrationsFrom(module_path('OrgMatrix', 'database/migrations'));
        $this->loadViewsFrom(module_path('OrgMatrix', 'resources/views'), 'orgmatrix');
    }
}
