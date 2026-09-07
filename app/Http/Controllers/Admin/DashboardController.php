<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\GlossaryTerm;
use App\Models\Module;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Post;
use App\Models\SupportTicket;
use App\Models\Team;
use App\Models\ToolSubscription;
use App\Models\User;
use Modules\AuditPro\Models\Audit;
use Modules\FinancialPlatform\Models\Analysis;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Safe count helper for optional module models
        $safeCount = function ($class, $callback = null) {
            try {
                if (class_exists($class)) {
                    $query = $class::query();
                    if ($callback) {
                        $callback($query);
                    }
                    return $query->count();
                }
            } catch (\Throwable $e) {
                return 0;
            }
            return 0;
        };

        $stats = [
            'users' => User::count(),
            'admins' => User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->count(),
            'teams' => Team::count(),
            'modules' => Module::where('is_active', true)->count(),
            'plans' => Plan::count(),
            'subscriptions' => ToolSubscription::count(),
            'active_subscriptions' => ToolSubscription::where('status', 'active')->count(),
            'pending_bank' => ToolSubscription::where('payment_method', 'bank')->where('status', 'pending')->count(),
            'analyses' => Analysis::withoutGlobalScope('current_team')->count(),
            'audits' => Audit::withoutGlobalScope('current_team')->count(),
            'glossary' => GlossaryTerm::count(),
            'posts' => Post::count(),
            'pages' => Page::count(),
            'tickets' => SupportTicket::where('status', '!=', 'closed')->count(),
            'logs' => ActivityLog::count(),
            // Knowledge Growth OS stats
            'books' => $safeCount(\Modules\BookIntelligence\app\Models\Book::class),
            'gaps' => $safeCount(\Modules\BookIntelligence\app\Models\KnowledgeGap::class, fn ($q) => $q->where('status', 'open')),
            'content_opps' => $safeCount(\Modules\BookIntelligence\app\Models\ContentOpportunity::class),
            'generated_blogs' => $safeCount(\Modules\BookIntelligence\app\Models\GeneratedBlog::class),
            'competency_roles' => $safeCount(\Modules\BookIntelligence\app\Models\CompetencyRole::class),
            'learning_paths' => $safeCount(\Modules\BookIntelligence\app\Models\LearningPath::class),
        ];

        $recentUsers = User::with('currentTeam')->latest()->limit(8)->get();
        $recentSubscriptions = ToolSubscription::with(['plan', 'billable'])->latest()->limit(8)->get();
        $recentAnalyses = Analysis::withoutGlobalScope('current_team')->with(['company', 'team'])->latest()->limit(8)->get();
        $recentAudits = Audit::withoutGlobalScope('current_team')->with(['template', 'team'])->latest()->limit(8)->get();
        $recentTickets = SupportTicket::with('user')->latest()->limit(6)->get();

        $activeModulesList = Module::where('is_active', true)->get();

        return view('admin.index', compact(
            'stats',
            'recentUsers',
            'recentSubscriptions',
            'recentAnalyses',
            'recentAudits',
            'recentTickets',
            'activeModulesList'
        ));
    }
}

