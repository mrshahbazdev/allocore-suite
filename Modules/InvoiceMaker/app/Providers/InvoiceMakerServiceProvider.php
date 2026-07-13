<?php

namespace Modules\InvoiceMaker\Providers;

use App\Support\DashboardWidgetRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Modules\InvoiceMaker\Console\Commands\ProcessRecurringInvoices;
use Modules\InvoiceMaker\Console\Commands\UpdateOverdueInvoices;
use Modules\InvoiceMaker\Livewire\Accounting\CashBook;
use Modules\InvoiceMaker\Livewire\Clients\Form as ClientForm;
use Modules\InvoiceMaker\Livewire\Clients\Index as ClientIndex;
use Modules\InvoiceMaker\Livewire\Dashboard;
use Modules\InvoiceMaker\Livewire\Documents\Form as DocumentForm;
use Modules\InvoiceMaker\Livewire\Documents\Index as DocumentIndex;
use Modules\InvoiceMaker\Livewire\Documents\Show as DocumentShow;
use Modules\InvoiceMaker\Livewire\Expenses\Index as ExpenseIndex;
use Modules\InvoiceMaker\Livewire\Products\Form as ProductForm;
use Modules\InvoiceMaker\Livewire\Products\Index as ProductIndex;
use Modules\InvoiceMaker\Livewire\Settings\Profile as SettingsProfile;
use Modules\InvoiceMaker\Livewire\Templates\Index as TemplateIndex;
use Modules\InvoiceMaker\Services\DashboardSnapshot;
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

        Livewire::component('invoicemaker.dashboard', Dashboard::class);
        Livewire::component('invoicemaker.clients.index', ClientIndex::class);
        Livewire::component('invoicemaker.clients.form', ClientForm::class);
        Livewire::component('invoicemaker.documents.index', DocumentIndex::class);
        Livewire::component('invoicemaker.documents.form', DocumentForm::class);
        Livewire::component('invoicemaker.documents.show', DocumentShow::class);
        Livewire::component('invoicemaker.expenses.index', ExpenseIndex::class);
        Livewire::component('invoicemaker.products.index', ProductIndex::class);
        Livewire::component('invoicemaker.products.form', ProductForm::class);
        Livewire::component('invoicemaker.accounting.cash-book', CashBook::class);
        Livewire::component('invoicemaker.settings.profile', SettingsProfile::class);
        Livewire::component('invoicemaker.templates.index', TemplateIndex::class);
    }

    public function boot(): void
    {
        parent::boot();

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
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('invoicemaker:process-recurring')->dailyAt('00:10');
        $schedule->command('invoicemaker:update-overdue')->dailyAt('00:20');
    }
}
