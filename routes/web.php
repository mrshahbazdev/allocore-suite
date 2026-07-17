<?php

use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinancialController as AdminFinancialController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SubscriptionApprovalController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\Admin\ThresholdController as AdminThresholdController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TeamController;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('language/{locale}', LanguageController::class)->name('language')->whereIn('locale', config('app.available_locales', ['en']));

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
    })->whereIn('prefix', ['clusters', 'leads'])->name('modules.placeholder');

    // Teams
    Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    Route::post('teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('index');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/role', [AdminUserController::class, 'role'])->name('users.role');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('teams', [AdminTeamController::class, 'index'])->name('teams.index');
    Route::get('teams/{team}', [AdminTeamController::class, 'show'])->name('teams.show');
    Route::put('teams/{team}', [AdminTeamController::class, 'update'])->name('teams.update');
    Route::delete('teams/{team}', [AdminTeamController::class, 'destroy'])->name('teams.destroy');
    Route::delete('teams/{team}/members/{user}', [AdminTeamController::class, 'removeMember'])->name('teams.members.remove');
    Route::get('modules', [AdminModuleController::class, 'index'])->name('modules.index');
    Route::put('modules/{module}', [AdminModuleController::class, 'update'])->name('modules.update');
    Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
    Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
    Route::get('subscriptions', [SubscriptionApprovalController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions/{subscription}/approve', [SubscriptionApprovalController::class, 'approve'])->name('subscriptions.approve');
    Route::post('subscriptions/{subscription}/reject', [SubscriptionApprovalController::class, 'reject'])->name('subscriptions.reject');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionApprovalController::class, 'cancel'])->name('subscriptions.cancel');
    Route::get('audits', [AdminAuditController::class, 'index'])->name('audits.index');
    Route::get('audits/{audit}', [AdminAuditController::class, 'show'])->name('audits.show');
    Route::get('financial', [AdminFinancialController::class, 'index'])->name('financial.index');
    Route::get('thresholds', [AdminThresholdController::class, 'index'])->name('thresholds.index');
    Route::put('thresholds/{threshold}', [AdminThresholdController::class, 'update'])->name('thresholds.update');
    Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

    Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
    Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
    Route::post('pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::delete('pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');
    Route::post('pages/reorder', [AdminPageController::class, 'reorder'])->name('pages.reorder');
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

Route::get('pages/{slug}', [PageController::class, 'show'])->name('page.show');
