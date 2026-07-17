<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\AuditPillarController as AdminAuditPillarController;
use App\Http\Controllers\Admin\AuditQuestionController as AdminAuditQuestionController;
use App\Http\Controllers\Admin\AuditTemplateController as AdminAuditTemplateController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinancialController as AdminFinancialController;
use App\Http\Controllers\Admin\IntegrationController as AdminIntegrationController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\SubscriptionApprovalController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\Admin\ThresholdController as AdminThresholdController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserSubscriptionController as AdminUserSubscriptionController;
use App\Http\Controllers\Admin\WebhookController as AdminWebhookController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TeamController;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

Route::bind('template', fn ($value) => AuditTemplate::withoutGlobalScope('current_team')->findOrFail($value));
Route::bind('pillar', fn ($value) => AuditPillar::withoutGlobalScope('current_team')->findOrFail($value));
Route::bind('question', fn ($value) => AuditQuestion::withoutGlobalScope('current_team')->findOrFail($value));

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
    Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/role', [AdminUserController::class, 'role'])->name('users.role');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('users/{user}/subscriptions', [AdminUserSubscriptionController::class, 'index'])->name('users.subscriptions.index');
    Route::post('users/{user}/subscriptions', [AdminUserSubscriptionController::class, 'store'])->name('users.subscriptions.store');
    Route::put('users/{user}/subscriptions/{subscription}', [AdminUserSubscriptionController::class, 'update'])->name('users.subscriptions.update');
    Route::post('users/{user}/subscriptions/{subscription}/approve', [AdminUserSubscriptionController::class, 'approve'])->name('users.subscriptions.approve');
    Route::post('users/{user}/subscriptions/{subscription}/cancel', [AdminUserSubscriptionController::class, 'cancel'])->name('users.subscriptions.cancel');
    Route::delete('users/{user}/subscriptions/{subscription}', [AdminUserSubscriptionController::class, 'destroy'])->name('users.subscriptions.destroy');

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

    Route::get('audits/templates', [AdminAuditTemplateController::class, 'index'])->name('audits.templates.index');
    Route::get('audits/templates/create', [AdminAuditTemplateController::class, 'create'])->name('audits.templates.create');
    Route::post('audits/templates', [AdminAuditTemplateController::class, 'store'])->name('audits.templates.store');
    Route::get('audits/templates/{template}', [AdminAuditTemplateController::class, 'show'])->name('audits.templates.show');
    Route::get('audits/templates/{template}/edit', [AdminAuditTemplateController::class, 'edit'])->name('audits.templates.edit');
    Route::put('audits/templates/{template}', [AdminAuditTemplateController::class, 'update'])->name('audits.templates.update');
    Route::delete('audits/templates/{template}', [AdminAuditTemplateController::class, 'destroy'])->name('audits.templates.destroy');

    Route::get('audits/pillars/create', [AdminAuditPillarController::class, 'create'])->name('audits.pillars.create');
    Route::post('audits/pillars', [AdminAuditPillarController::class, 'store'])->name('audits.pillars.store');
    Route::get('audits/pillars/{pillar}/edit', [AdminAuditPillarController::class, 'edit'])->name('audits.pillars.edit');
    Route::put('audits/pillars/{pillar}', [AdminAuditPillarController::class, 'update'])->name('audits.pillars.update');
    Route::delete('audits/pillars/{pillar}', [AdminAuditPillarController::class, 'destroy'])->name('audits.pillars.destroy');

    Route::get('audits/questions/create', [AdminAuditQuestionController::class, 'create'])->name('audits.questions.create');
    Route::post('audits/questions', [AdminAuditQuestionController::class, 'store'])->name('audits.questions.store');
    Route::get('audits/questions/{question}/edit', [AdminAuditQuestionController::class, 'edit'])->name('audits.questions.edit');
    Route::put('audits/questions/{question}', [AdminAuditQuestionController::class, 'update'])->name('audits.questions.update');
    Route::delete('audits/questions/{question}', [AdminAuditQuestionController::class, 'destroy'])->name('audits.questions.destroy');

    Route::get('invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');

    Route::get('activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('activity-logs/{activityLog}', [AdminActivityLogController::class, 'show'])->name('activity-logs.show');

    Route::get('roles', [AdminRoleController::class, 'index'])->name('roles.index');
    Route::get('roles/create', [AdminRoleController::class, 'create'])->name('roles.create');
    Route::post('roles', [AdminRoleController::class, 'store'])->name('roles.store');
    Route::get('roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [AdminRoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('support-tickets', [AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::put('support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'update'])->name('support-tickets.update');
    Route::post('support-tickets/{supportTicket}/messages', [AdminSupportTicketController::class, 'storeMessage'])->name('support-tickets.messages.store');
    Route::delete('support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'destroy'])->name('support-tickets.destroy');

    Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('integrations', [AdminIntegrationController::class, 'index'])->name('integrations.index');
    Route::get('integrations/create', [AdminIntegrationController::class, 'create'])->name('integrations.create');
    Route::post('integrations', [AdminIntegrationController::class, 'store'])->name('integrations.store');
    Route::get('integrations/{integration}/edit', [AdminIntegrationController::class, 'edit'])->name('integrations.edit');
    Route::put('integrations/{integration}', [AdminIntegrationController::class, 'update'])->name('integrations.update');
    Route::delete('integrations/{integration}', [AdminIntegrationController::class, 'destroy'])->name('integrations.destroy');

    Route::get('webhooks/create', [AdminWebhookController::class, 'create'])->name('webhooks.create');
    Route::post('webhooks', [AdminWebhookController::class, 'store'])->name('webhooks.store');
    Route::get('webhooks/{webhook}/edit', [AdminWebhookController::class, 'edit'])->name('webhooks.edit');
    Route::put('webhooks/{webhook}', [AdminWebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('webhooks/{webhook}', [AdminWebhookController::class, 'destroy'])->name('webhooks.destroy');

    Route::get('announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [AdminAnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
    Route::post('media', [AdminMediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

    Route::get('backups', [AdminBackupController::class, 'index'])->name('backups.index');
    Route::post('backups', [AdminBackupController::class, 'createSqlDump'])->name('backups.store');
    Route::get('backups/export/users', [AdminBackupController::class, 'exportUsers'])->name('backups.export.users');
    Route::get('backups/export/teams', [AdminBackupController::class, 'exportTeams'])->name('backups.export.teams');
    Route::get('backups/export/invoices', [AdminBackupController::class, 'exportInvoices'])->name('backups.export.invoices');
    Route::get('backups/export/payments', [AdminBackupController::class, 'exportPayments'])->name('backups.export.payments');
    Route::get('backups/{backup}/download', [AdminBackupController::class, 'download'])->name('backups.download');
    Route::delete('backups/{backup}', [AdminBackupController::class, 'destroy'])->name('backups.destroy');

    Route::get('financial', [AdminFinancialController::class, 'index'])->name('financial.index');
    Route::get('thresholds', [AdminThresholdController::class, 'index'])->name('thresholds.index');
    Route::put('thresholds/{threshold}', [AdminThresholdController::class, 'update'])->name('thresholds.update');
    Route::get('settings', [SiteSettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

    Route::get('mail-settings', [MailSettingController::class, 'index'])->name('mail-settings.index');
    Route::put('mail-settings', [MailSettingController::class, 'update'])->name('mail-settings.update');

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
