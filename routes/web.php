<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ApiTokenController as AdminApiTokenController;
use App\Http\Controllers\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Admin\AuditPillarController as AdminAuditPillarController;
use App\Http\Controllers\Admin\AuditQuestionController as AdminAuditQuestionController;
use App\Http\Controllers\Admin\AuditTemplateController as AdminAuditTemplateController;
use App\Http\Controllers\Admin\BackupController as AdminBackupController;
use App\Http\Controllers\Admin\BillingDashboardController as AdminBillingDashboardController;
use App\Http\Controllers\Admin\BlogCategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\BlogTagController as AdminBlogTagController;
use App\Http\Controllers\Admin\BulkUserController as AdminBulkUserController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EnvController as AdminEnvController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FinancialController as AdminFinancialController;
use App\Http\Controllers\Admin\ImpersonationController as AdminImpersonationController;
use App\Http\Controllers\Admin\IntegrationController as AdminIntegrationController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\LogViewerController as AdminLogViewerController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\ModuleDataController as AdminModuleDataController;
use App\Http\Controllers\Admin\NotificationTemplateController as AdminNotificationTemplateController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\QueueMonitorController as AdminQueueMonitorController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SessionManagerController as AdminSessionManagerController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StatusIncidentController as AdminStatusIncidentController;
use App\Http\Controllers\Admin\SubscriptionApprovalController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\TaxRateController as AdminTaxRateController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\Admin\ThresholdController as AdminThresholdController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserSubscriptionController as AdminUserSubscriptionController;
use App\Http\Controllers\Admin\WebhookController as AdminWebhookController;
use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\ApiDocsController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CookieConsentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardExportController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ModuleFallbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use App\Http\Controllers\NotificationStreamController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\ScheduledReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatusPageController;
use App\Http\Controllers\TeamBrandingController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\TeamMemberPermissionController;
use App\Http\Controllers\TeamSecurityController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\ToolAnalyzerController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\TwoFactorChallengeController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UsageAnalyticsController;
use App\Http\Controllers\UserActivityController;
use App\Http\Controllers\UserApiTokenController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Support\Facades\Route;
use Modules\AuditPro\Models\AuditPillar;
use Modules\AuditPro\Models\AuditQuestion;
use Modules\AuditPro\Models\AuditTemplate;

Route::bind('template', fn ($value) => AuditTemplate::withoutGlobalScope('current_team')->findOrFail($value));
Route::bind('pillar', fn ($value) => AuditPillar::withoutGlobalScope('current_team')->findOrFail($value));
Route::bind('question', fn ($value) => AuditQuestion::withoutGlobalScope('current_team')->findOrFail($value));

Route::get('install', [InstallController::class, 'index'])->name('install.index');
Route::get('install/database', [InstallController::class, 'database'])->name('install.database');
Route::post('install/database', [InstallController::class, 'storeDatabase'])->name('install.database.store');
Route::get('install/admin', [InstallController::class, 'admin'])->name('install.admin');
Route::post('install/run', [InstallController::class, 'run'])->name('install.run');

Route::view('/', 'welcome');
Route::view('/offline', 'offline')->name('offline');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', OnboardingController::class)->name('onboarding.index');
    Route::post('/onboarding/team', [OnboardingController::class, 'storeTeam'])->name('onboarding.team');
    Route::post('/onboarding/plan', [OnboardingController::class, 'storePlan'])->name('onboarding.plan');
    Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    Route::get('marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
    Route::get('marketplace/{module}', [MarketplaceController::class, 'show'])->name('marketplace.show');
});

Route::get('language/{locale}', LanguageController::class)->name('language')->whereIn('locale', config('app.available_locales', ['en']));
Route::post('cookie-consent', [CookieConsentController::class, 'store'])->name('cookie-consent.store');

Route::get('search', GlobalSearchController::class)->name('search');
Route::get('sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('help', [HelpController::class, 'index'])->name('help.index');
Route::get('status', [StatusPageController::class, 'index'])->name('status.index');
Route::get('api-docs', ApiDocsController::class)->name('api-docs.index');

Route::get('two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
Route::post('two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.challenge.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('two-factor', [TwoFactorController::class, 'index'])->name('two-factor.index');
    Route::post('two-factor', [TwoFactorController::class, 'store'])->name('two-factor.store');
    Route::delete('two-factor', [TwoFactorController::class, 'destroy'])->name('two-factor.destroy');
    Route::post('two-factor/regenerate', [TwoFactorController::class, 'regenerate'])->name('two-factor.regenerate');

    Route::get('profile/activity', [UserActivityController::class, 'index'])->name('profile.activity');
    Route::get('profile/api-tokens', [UserApiTokenController::class, 'index'])->name('profile.api-tokens.index');
    Route::post('profile/api-tokens', [UserApiTokenController::class, 'store'])->name('profile.api-tokens.store');
    Route::delete('profile/api-tokens/{token}', [UserApiTokenController::class, 'destroy'])->name('profile.api-tokens.destroy');

    Route::get('stop-impersonating', [AdminImpersonationController::class, 'stop'])->name('impersonation.stop');

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('dashboard/export/pdf', [DashboardExportController::class, 'pdf'])->name('dashboard.export.pdf');
    Route::get('tools', ToolsController::class)->name('tools.index');
    Route::get('recommendations', RecommendationController::class)->name('recommendations.index');
    Route::get('workspace', WorkspaceController::class)->name('workspace.index');
    Route::get('timeline', [TimelineController::class, 'index'])->name('timeline.index');
    Route::get('search', SearchController::class)->name('search.index');
    Route::get('imports', [ImportController::class, 'index'])->name('imports.index');
    Route::post('imports/upload', [ImportController::class, 'upload'])->name('imports.upload');
    Route::post('imports', [ImportController::class, 'store'])->name('imports.store');
    Route::get('advisor', AdvisorController::class)->name('advisor.index');
    Route::get('usage', UsageAnalyticsController::class)->name('usage.index');
    Route::resource('dashboards', UserDashboardController::class)->names('dashboards');
    Route::post('dashboards/reorder', [UserDashboardController::class, 'reorder'])->name('dashboards.reorder');

    Route::resource('scheduled-reports', ScheduledReportController::class)->names('scheduled-reports');
    Route::get('assistant', [AiAssistantController::class, 'index'])->name('assistant.index');
    Route::post('assistant', [AiAssistantController::class, 'store'])->name('assistant.store');
    Route::delete('assistant', [AiAssistantController::class, 'destroy'])->name('assistant.destroy');

    Route::get('workflows', [WorkflowController::class, 'index'])->name('workflows.index');
    Route::get('workflows/create', [WorkflowController::class, 'create'])->name('workflows.create');
    Route::post('workflows', [WorkflowController::class, 'store'])->name('workflows.store');
    Route::get('workflows/{workflow}/edit', [WorkflowController::class, 'edit'])->name('workflows.edit');
    Route::patch('workflows/{workflow}', [WorkflowController::class, 'update'])->name('workflows.update');
    Route::delete('workflows/{workflow}', [WorkflowController::class, 'destroy'])->name('workflows.destroy');

    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('alerts/create', [AlertController::class, 'create'])->name('alerts.create');
    Route::post('alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::get('alerts/{alert}/edit', [AlertController::class, 'edit'])->name('alerts.edit');
    Route::patch('alerts/{alert}', [AlertController::class, 'update'])->name('alerts.update');
    Route::delete('alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');
    Route::post('alerts/{alert}/test', [AlertController::class, 'test'])->name('alerts.test');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/stream', NotificationStreamController::class)->name('notifications.stream');
    Route::get('notifications/preferences', [NotificationPreferenceController::class, 'index'])->name('notifications.preferences');
    Route::patch('notifications/preferences', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Billing
    Route::get('billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
    Route::get('billing/subscriptions', [BillingController::class, 'subscriptions'])->name('billing.subscriptions');
    Route::post('billing/checkout/{plan}', [BillingController::class, 'checkout'])->name('billing.checkout');
    Route::get('billing/stripe/success/{subscription}', [BillingController::class, 'stripeSuccess'])->name('billing.stripe.success');
    Route::get('billing/paypal/success/{subscription}', [BillingController::class, 'paypalSuccess'])->name('billing.paypal.success');
    Route::get('billing/bank/{subscription}', [BillingController::class, 'bank'])->name('billing.bank');
    Route::post('billing/bank/{subscription}', [BillingController::class, 'bankSubmit'])->name('billing.bank.submit');

    Route::get('analyze', ToolAnalyzerController::class)->name('tool-analyzer.index');

    // Module placeholders — replaced by real module routes as each module is ported
    Route::get('app/{prefix}', ModuleFallbackController::class)
        ->whereIn('prefix', ['leads'])
        ->name('modules.placeholder');

    // Teams
    Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');
    Route::get('teams/{team}/branding', [TeamBrandingController::class, 'edit'])->name('teams.branding.edit');
    Route::patch('teams/{team}/branding', [TeamBrandingController::class, 'update'])->name('teams.branding.update');
    Route::get('teams/{team}/security', [TeamSecurityController::class, 'edit'])->name('teams.security.edit');
    Route::patch('teams/{team}/security', [TeamSecurityController::class, 'update'])->name('teams.security.update');
    Route::post('teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
    Route::get('teams/{team}/members/{member}/permissions', [TeamMemberPermissionController::class, 'edit'])->name('teams.members.permissions.edit');
    Route::patch('teams/{team}/members/{member}/permissions', [TeamMemberPermissionController::class, 'update'])->name('teams.members.permissions.update');
    Route::post('teams/{team}/invitations', [TeamInvitationController::class, 'store'])->name('teams.invitations.store');
    Route::post('teams/invitations/{invitation}/resend', [TeamInvitationController::class, 'resend'])->name('teams.invitations.resend');
    Route::delete('teams/invitations/{invitation}', [TeamInvitationController::class, 'destroy'])->name('teams.invitations.destroy');
});

Route::get('teams/invitations/{token}/accept', [TeamInvitationController::class, 'accept'])->name('teams.invitations.accept');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('index');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/impersonate', [AdminImpersonationController::class, 'impersonate'])->name('users.impersonate');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/role', [AdminUserController::class, 'role'])->name('users.role');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::patch('users/bulk', [AdminBulkUserController::class, 'update'])->name('users.bulk');

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

    Route::get('module-data/{group}/{resource}', [AdminModuleDataController::class, 'index'])->name('module-data.index');
    Route::get('module-data/{group}/{resource}/{id}', [AdminModuleDataController::class, 'show'])->name('module-data.show');
    Route::delete('module-data/{group}/{resource}/{id}', [AdminModuleDataController::class, 'destroy'])->name('module-data.destroy');

    Route::get('invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::get('billing', AdminBillingDashboardController::class)->name('billing.index');

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
    Route::get('webhooks/{webhook}/history', [AdminWebhookController::class, 'history'])->name('webhooks.history');
    Route::post('webhook-calls/{webhookCall}/retry', [AdminWebhookController::class, 'retry'])->name('webhook-calls.retry');

    Route::get('announcements', [AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [AdminAnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}/edit', [AdminAnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [AdminAnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
    Route::post('media', [AdminMediaController::class, 'store'])->name('media.store');
    Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');

    Route::get('maintenance', [AdminMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::put('maintenance', [AdminMaintenanceController::class, 'update'])->name('maintenance.update');

    Route::resource('api-tokens', AdminApiTokenController::class)->names('api-tokens')->only(['index', 'create', 'store', 'destroy']);
    Route::resource('coupons', AdminCouponController::class)->names('coupons');
    Route::resource('tax-rates', AdminTaxRateController::class)->names('tax-rates');
    Route::resource('notification-templates', AdminNotificationTemplateController::class)->names('notification-templates');
    Route::get('queue-monitor', [AdminQueueMonitorController::class, 'index'])->name('queue-monitor.index');

    Route::get('notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/create', [AdminNotificationController::class, 'create'])->name('notifications.create');
    Route::post('notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
    Route::delete('notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('log-viewer', [AdminLogViewerController::class, 'index'])->name('log-viewer.index');
    Route::get('session-manager', [AdminSessionManagerController::class, 'index'])->name('session-manager.index');
    Route::delete('session-manager/{id}', [AdminSessionManagerController::class, 'destroy'])->name('session-manager.destroy');

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

    Route::get('env', [AdminEnvController::class, 'index'])->name('env.index');
    Route::put('env', [AdminEnvController::class, 'update'])->name('env.update');

    Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');

    Route::resource('blog/posts', AdminBlogPostController::class)->names('blog.posts');
    Route::resource('blog/categories', AdminBlogCategoryController::class)->names('blog.categories');
    Route::resource('blog/tags', AdminBlogTagController::class)->names('blog.tags');
    Route::get('blog/comments', [AdminBlogCommentController::class, 'index'])->name('blog.comments.index');
    Route::patch('blog/comments/{comment}/approve', [AdminBlogCommentController::class, 'approve'])->name('blog.comments.approve');
    Route::delete('blog/comments/{comment}', [AdminBlogCommentController::class, 'destroy'])->name('blog.comments.destroy');

    Route::get('status-incidents', [AdminStatusIncidentController::class, 'index'])->name('status-incidents.index');
    Route::get('status-incidents/create', [AdminStatusIncidentController::class, 'create'])->name('status-incidents.create');
    Route::post('status-incidents', [AdminStatusIncidentController::class, 'store'])->name('status-incidents.store');
    Route::get('status-incidents/{statusIncident}/edit', [AdminStatusIncidentController::class, 'edit'])->name('status-incidents.edit');
    Route::put('status-incidents/{statusIncident}', [AdminStatusIncidentController::class, 'update'])->name('status-incidents.update');
    Route::delete('status-incidents/{statusIncident}', [AdminStatusIncidentController::class, 'destroy'])->name('status-incidents.destroy');
    Route::patch('status-incidents/{statusIncident}/resolve', [AdminStatusIncidentController::class, 'resolve'])->name('status-incidents.resolve');
    Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
    Route::post('pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::delete('pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');
    Route::post('pages/reorder', [AdminPageController::class, 'reorder'])->name('pages.reorder');

    Route::get('exports', [ExportController::class, 'index'])->name('exports.index');
    Route::get('exports/download', [ExportController::class, 'export'])->name('exports.download');
});

Route::post('logout', LogoutController::class)
    ->middleware('auth')
    ->name('logout');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('pages/{slug}', [PageController::class, 'show'])->name('page.show');

Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('blog/feed', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('blog/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('blog/tag/{tag}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('blog/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::post('blog/{post}/comments', [BlogController::class, 'storeComment'])->name('blog.comments.store');
