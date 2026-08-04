<?php

namespace Modules\InvoiceMaker\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Modules\InvoiceMaker\Console\Commands\ProcessRecurringInvoices;
use Modules\InvoiceMaker\Console\Commands\SendInvoiceReminders;
use Modules\InvoiceMaker\Console\Commands\SendScheduledInvoices;
use Modules\InvoiceMaker\Console\Commands\UpdateOverdueInvoices;
use Modules\InvoiceMaker\Livewire\Accounting\CashBook\Index as CashBookIndex;
use Modules\InvoiceMaker\Livewire\Accounting\Categories\Index as CategoriesIndex;
use Modules\InvoiceMaker\Livewire\Accounting\Reconciliation;
use Modules\InvoiceMaker\Livewire\Clients\Create as ClientsCreate;
use Modules\InvoiceMaker\Livewire\Clients\Edit as ClientsEdit;
use Modules\InvoiceMaker\Livewire\Clients\Index as ClientsIndex;
use Modules\InvoiceMaker\Livewire\Dashboard;
use Modules\InvoiceMaker\Livewire\Estimates\Create as EstimatesCreate;
use Modules\InvoiceMaker\Livewire\Estimates\Edit as EstimatesEdit;
use Modules\InvoiceMaker\Livewire\Estimates\Index as EstimatesIndex;
use Modules\InvoiceMaker\Livewire\Expenses\Create as ExpensesCreate;
use Modules\InvoiceMaker\Livewire\Expenses\Edit as ExpensesEdit;
use Modules\InvoiceMaker\Livewire\Expenses\Index as ExpensesIndex;
use Modules\InvoiceMaker\Livewire\Expenses\Show as ExpensesShow;
use Modules\InvoiceMaker\Livewire\Invoices\Create as InvoicesCreate;
use Modules\InvoiceMaker\Livewire\Invoices\Edit as InvoicesEdit;
use Modules\InvoiceMaker\Livewire\Invoices\Index as InvoicesIndex;
use Modules\InvoiceMaker\Livewire\Invoices\Show as InvoicesShow;
use Modules\InvoiceMaker\Livewire\Products\Create as ProductsCreate;
use Modules\InvoiceMaker\Livewire\Products\Edit as ProductsEdit;
use Modules\InvoiceMaker\Livewire\Products\Index as ProductsIndex;
use Modules\InvoiceMaker\Livewire\Reports\Profitability;
use Modules\InvoiceMaker\Livewire\Settings\EmailTemplates;
use Modules\InvoiceMaker\Livewire\Settings\Profile as SettingsProfile;
use Modules\InvoiceMaker\Livewire\Settings\Team;
use Modules\InvoiceMaker\Livewire\Templates\Builder as TemplatesBuilder;
use Modules\InvoiceMaker\Livewire\Templates\Index as TemplatesIndex;
use Modules\InvoiceMaker\Services\DashboardSnapshot;
use Modules\InvoiceMaker\Services\InvoiceMakerContext;
use Nwidart\Modules\Support\ModuleServiceProvider;

class InvoiceMakerServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'InvoiceMaker';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'invoicemaker';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    protected array $commands = [
        ProcessRecurringInvoices::class,
        SendInvoiceReminders::class,
        SendScheduledInvoices::class,
        UpdateOverdueInvoices::class,
    ];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(InvoiceMakerContext::class);

        Livewire::component('invoicemaker.dashboard', Dashboard::class);

        Livewire::component('invoicemaker.clients.index', ClientsIndex::class);
        Livewire::component('invoicemaker.clients.create', ClientsCreate::class);
        Livewire::component('invoicemaker.clients.edit', ClientsEdit::class);

        Livewire::component('invoicemaker.products.index', ProductsIndex::class);
        Livewire::component('invoicemaker.products.create', ProductsCreate::class);
        Livewire::component('invoicemaker.products.edit', ProductsEdit::class);

        Livewire::component('invoicemaker.invoices.index', InvoicesIndex::class);
        Livewire::component('invoicemaker.invoices.create', InvoicesCreate::class);
        Livewire::component('invoicemaker.invoices.edit', InvoicesEdit::class);
        Livewire::component('invoicemaker.invoices.show', InvoicesShow::class);

        Livewire::component('invoicemaker.estimates.index', EstimatesIndex::class);
        Livewire::component('invoicemaker.estimates.create', EstimatesCreate::class);
        Livewire::component('invoicemaker.estimates.edit', EstimatesEdit::class);

        Livewire::component('invoicemaker.expenses.index', ExpensesIndex::class);
        Livewire::component('invoicemaker.expenses.create', ExpensesCreate::class);
        Livewire::component('invoicemaker.expenses.edit', ExpensesEdit::class);
        Livewire::component('invoicemaker.expenses.show', ExpensesShow::class);

        Livewire::component('invoicemaker.accounting.cash-book.index', CashBookIndex::class);
        Livewire::component('invoicemaker.accounting.categories.index', CategoriesIndex::class);
        Livewire::component('invoicemaker.accounting.reconciliation', Reconciliation::class);

        Livewire::component('invoicemaker.reports.profitability', Profitability::class);

        Livewire::component('invoicemaker.settings.email-templates', EmailTemplates::class);
        Livewire::component('invoicemaker.settings.team', Team::class);
        Livewire::component('invoicemaker.settings.profile', SettingsProfile::class);

        Livewire::component('invoicemaker.templates.index', TemplatesIndex::class);
        Livewire::component('invoicemaker.templates.builder', TemplatesBuilder::class);
    }

    public function boot(): void
    {
        parent::boot();

        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            $model = $arguments[0] ?? null;

            if (! $model instanceof Model) {
                return null;
            }

            if (! str_starts_with(get_class($model), 'Modules\\InvoiceMaker\\Models\\')) {
                return null;
            }

            if (! $user?->current_team_id) {
                return null;
            }

            return $user->current_team_id === $model->getAttribute('team_id') ? true : null;
        });

        Blade::anonymousComponentNamespace('invoicemaker::components', 'invoicemaker');

        app(DashboardWidgetRegistry::class)->register(
            'invoice-maker',
            'invoicemaker::dashboard-widget',
            20,
        );

        View::composer('invoicemaker::dashboard-widget', function ($view): void {
            $view->with(
                app(DashboardSnapshot::class)->forTeam(auth()->user()?->currentTeam)
            );
        });

        View::composer('invoicemaker::*', function ($view): void {
            if (! auth()->check() || $view->getName() === 'invoicemaker::invoices.public') {
                return;
            }

            $view->with('profile', app(InvoiceMakerContext::class)->profile());
        });
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('invoicemaker:send-scheduled')->everyMinute();
        $schedule->command('invoicemaker:process-recurring')->dailyAt('00:10');
        $schedule->command('invoicemaker:update-overdue')->dailyAt('00:20');
        $schedule->command('invoicemaker:send-reminders')->weekly();
    }
}
