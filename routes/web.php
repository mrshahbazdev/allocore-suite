<?php

use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionApprovalController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeamController;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Billing
    Route::get('billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::get('billing/subscriptions', [BillingController::class, 'subscriptions'])->name('billing.subscriptions');
    Route::post('billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('billing/stripe/success/{subscription}', [BillingController::class, 'stripeSuccess'])->name('billing.stripe.success');
    Route::get('billing/paypal/success/{subscription}', [BillingController::class, 'paypalSuccess'])->name('billing.paypal.success');
    Route::get('billing/bank/{subscription}', [BillingController::class, 'bank'])->name('billing.bank');
    Route::post('billing/bank/{subscription}', [BillingController::class, 'bankSubmit'])->name('billing.bank.submit');

    // Module placeholders — replaced by real module routes as each module is ported
    Route::get('app/{prefix}', function (string $prefix) {
        $module = Module::where('route_prefix', $prefix)->firstOrFail();
        abort_unless(auth()->user()->hasModule($module->key), 403);

        return view('modules.placeholder', compact('module'));
    })->whereIn('prefix', ['invoices', 'clusters', 'leads'])->name('modules.placeholder');

    // Teams
    Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    Route::post('teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::get('subscriptions', [SubscriptionApprovalController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/{subscription}/approve', [SubscriptionApprovalController::class, 'approve'])->name('subscriptions.approve');
    Route::post('subscriptions/{subscription}/reject', [SubscriptionApprovalController::class, 'reject'])->name('subscriptions.reject');
});

Route::post('logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
