<?php

namespace Modules\FocusMatrix\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class FocusMatrixServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'FocusMatrix';

    protected string $nameLower = 'focusmatrix';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->loadMigrationsFrom(module_path('FocusMatrix', 'database/migrations'));
        $this->loadViewsFrom(module_path('FocusMatrix', 'resources/views'), 'focusmatrix');
    }
}
