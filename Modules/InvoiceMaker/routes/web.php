<?php

use Illuminate\Support\Facades\Route;
use Modules\InvoiceMaker\Http\Controllers\InvoiceController;
use Modules\InvoiceMaker\Http\Controllers\InvoicePaymentController;
use Modules\InvoiceMaker\Http\Controllers\PublicInvoiceController;
use Modules\InvoiceMaker\Http\Middleware\EnsureCurrentTeam;
use Modules\InvoiceMaker\Livewire\Accounting\CashBook;
use Modules\InvoiceMaker\Livewire\Clients\Form as ClientForm;
use Modules\InvoiceMaker\Livewire\Clients\Index as ClientsIndex;
use Modules\InvoiceMaker\Livewire\Dashboard;
use Modules\InvoiceMaker\Livewire\Documents\Form as DocumentForm;
use Modules\InvoiceMaker\Livewire\Documents\Index as DocumentsIndex;
use Modules\InvoiceMaker\Livewire\Documents\Show as DocumentShow;
use Modules\InvoiceMaker\Livewire\Expenses\Index as ExpensesIndex;
use Modules\InvoiceMaker\Livewire\Products\Form as ProductForm;
use Modules\InvoiceMaker\Livewire\Products\Index as ProductsIndex;
use Modules\InvoiceMaker\Livewire\Settings\Profile as SettingsProfile;
use Modules\InvoiceMaker\Livewire\Templates\Index as TemplatesIndex;

Route::prefix('app/invoices')
    ->name('invoicemaker.')
    ->middleware(['auth', 'verified', 'module:invoice-maker', EnsureCurrentTeam::class])
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        Route::get('/clients', ClientsIndex::class)->name('clients.index');
        Route::get('/clients/create', ClientForm::class)->name('clients.create');
        Route::get('/clients/{client}/edit', ClientForm::class)->name('clients.edit');

        Route::get('/products', ProductsIndex::class)->name('products.index');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');

        Route::get('/invoices', DocumentsIndex::class)
            ->defaults('type', 'invoice')
            ->name('invoices.index');
        Route::get('/invoices/create', DocumentForm::class)
            ->defaults('type', 'invoice')
            ->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', DocumentForm::class)
            ->defaults('type', 'invoice')
            ->name('invoices.edit');
        Route::get('/invoices/{invoice}', DocumentShow::class)->name('invoices.show');
        Route::get('/invoices/{invoice}/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        Route::get('/estimates', DocumentsIndex::class)
            ->defaults('type', 'estimate')
            ->name('estimates.index');
        Route::get('/estimates/create', DocumentForm::class)
            ->defaults('type', 'estimate')
            ->name('estimates.create');
        Route::get('/estimates/{invoice}/edit', DocumentForm::class)
            ->defaults('type', 'estimate')
            ->name('estimates.edit');
        Route::get('/estimates/{invoice}', DocumentShow::class)->name('estimates.show');

        Route::get('/expenses', ExpensesIndex::class)->name('expenses.index');
        Route::get('/cash-book', CashBook::class)->name('cash-book.index');
        Route::get('/templates', TemplatesIndex::class)->name('templates.index');
        Route::get('/settings', SettingsProfile::class)->name('settings.profile');
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
