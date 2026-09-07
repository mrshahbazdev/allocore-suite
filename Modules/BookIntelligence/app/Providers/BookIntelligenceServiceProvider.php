<?php

namespace Modules\BookIntelligence\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\View;
use Modules\BookIntelligence\Models\Book;
use Modules\BookIntelligence\Models\BookAnalysis;
use Modules\BookIntelligence\Models\ReadingProgress;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BookIntelligenceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'BookIntelligence';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'bookintelligence';

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
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        app(DashboardWidgetRegistry::class)->register(
            'book-intelligence',
            'bookintelligence::dashboard-widget',
            45,
        );

        View::composer('bookintelligence::dashboard-widget', function ($view): void {
            $view->with([
                'bookCount' => Book::count(),
                'analysisCount' => BookAnalysis::where('status', BookAnalysis::STATUS_COMPLETED)->count(),
                'readingCount' => ReadingProgress::where('user_id', auth()->id())
                    ->where('status', 'reading')
                    ->count(),
            ]);
        });
    }
}
