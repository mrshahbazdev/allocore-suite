<?php

namespace Modules\RevenuePlanner\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\View;
use Modules\RevenuePlanner\Models\RevenuePlan;
use Nwidart\Modules\Support\ModuleServiceProvider;

class RevenuePlannerServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'RevenuePlanner';
    protected string $nameLower = 'revenueplanner';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        app(DashboardWidgetRegistry::class)->register(
            'revenue-planner',
            'revenueplanner::dashboard-widget',
            42,
        );

        View::composer('revenueplanner::dashboard-widget', function ($view): void {
            $view->with([
                'planCount' => RevenuePlan::count(),
                'activePlan' => RevenuePlan::where('status', 'active')->latest()->first(),
            ]);
        });
    }
}
