<?php

namespace Modules\VisionFlow\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class VisionFlowServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'VisionFlow';

    protected string $nameLower = 'visionflow';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
        $this->loadMigrationsFrom(module_path('VisionFlow', 'database/migrations'));
        $this->loadViewsFrom(module_path('VisionFlow', 'resources/views'), 'visionflow');
    }
}
