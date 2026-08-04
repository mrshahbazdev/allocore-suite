<?php

use Illuminate\Support\Facades\Route;
use Modules\InvoiceMaker\Http\Controllers\CashBookExportController;
use Modules\InvoiceMaker\Http\Controllers\InvoiceController;
use Modules\InvoiceMaker\Http\Controllers\InvoicePaymentController;
use Modules\InvoiceMaker\Http\Controllers\ProfitabilityExportController;
use Modules\InvoiceMaker\Http\Controllers\PublicInvoiceController;
use Modules\InvoiceMaker\Http\Middleware\EnsureCurrentTeam;
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

Route::prefix('app/invoices')
    ->name('invoicemaker.')
    ->middleware(['auth', 'verified', 'module:invoice-maker', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('/clients', ClientsIndex::class)->name('clients.index');
        Route::get('/clients/create', ClientsCreate::class)->name('clients.create');
        Route::get('/clients/{client}/edit', ClientsEdit::class)->name('clients.edit');

        Route::get('/products', ProductsIndex::class)->name('products.index');
        Route::get('/products/create', ProductsCreate::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductsEdit::class)->name('products.edit');

        Route::get('/invoices', InvoicesIndex::class)->name('invoices.index');
        Route::get('/invoices/create', InvoicesCreate::class)->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', InvoicesEdit::class)->name('invoices.edit');
        Route::get('/invoices/{invoice}', InvoicesShow::class)->name('invoices.show');
        Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        Route::get('/estimates', EstimatesIndex::class)->name('estimates.index');
        Route::get('/estimates/create', EstimatesCreate::class)->name('estimates.create');
        Route::get('/estimates/{invoice}/edit', EstimatesEdit::class)->name('estimates.edit');

        Route::get('/expenses', ExpensesIndex::class)->name('expenses.index');
        Route::get('/expenses/create', ExpensesCreate::class)->name('expenses.create');
        Route::get('/expenses/{expense}/edit', ExpensesEdit::class)->name('expenses.edit');
        Route::get('/expenses/{expense}', ExpensesShow::class)->name('expenses.show');

        Route::get('/accounting/cash-book', CashBookIndex::class)->name('cash-book.index');
        Route::get('/accounting/categories', CategoriesIndex::class)->name('accounting.categories.index');
        Route::get('/accounting/reconciliation', Reconciliation::class)->name('accounting.reconciliation');

        Route::get('/reports/profitability', Profitability::class)->name('reports.profitability');

        Route::get('/settings/email-templates', EmailTemplates::class)->name('settings.email-templates');
        Route::get('/settings/team', Team::class)->name('settings.team');
        Route::get('/settings/profile', SettingsProfile::class)->name('settings.profile');

        Route::get('/templates', TemplatesIndex::class)->name('templates.index');
        Route::get('/templates/builder', TemplatesBuilder::class)->name('templates.builder');
        Route::get('/templates/{template}/builder', TemplatesBuilder::class)->name('templates.builder.edit');

        Route::get('/cash-book/export/csv', [CashBookExportController::class, 'exportCsv'])->name('cash-book.export.csv');
        Route::get('/cash-book/export/excel', [CashBookExportController::class, 'exportExcel'])->name('cash-book.export.excel');
        Route::get('/cash-book/export/pdf', [CashBookExportController::class, 'exportPdf'])->name('cash-book.export.pdf');
        Route::get('/reports/profitability/export', [ProfitabilityExportController::class, 'exportExcel'])->name('reports.profitability.export');
    });

Route::prefix('invoice/{uuid}')
    ->name('invoicemaker.public.')
    ->middleware('signed')
    ->group(function () {
        Route::get('/', [PublicInvoiceController::class, 'show'])->name('show');
        Route::get('/download', [PublicInvoiceController::class, 'download'])->name('download');
        Route::post('/approve', [PublicInvoiceController::class, 'approve'])->name('approve');
        Route::post('/revision', [PublicInvoiceController::class, 'requestRevision'])->name('revision');
        Route::post('/comment', [PublicInvoiceController::class, 'comment'])->name('comment');
        Route::post('/pay', [InvoicePaymentController::class, 'checkout'])->name('payment.checkout');
    });

Route::get('/invoice/{uuid}/payment/success', [InvoicePaymentController::class, 'success'])
    ->middleware('throttle:30,1')
    ->name('invoicemaker.public.payment.success');
