<?php

namespace Modules\AuditPro\Http\Controllers;

use App\Services\AllocoreBenchmarkService;
use App\Services\AllocoreRecommendationService;
use App\Services\AllocoreScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditResult;
use Modules\AuditPro\Models\AuditTemplate;
use Modules\AuditPro\Services\AuditPdfService;
use Modules\AuditPro\Services\DefaultTemplateProvisioner;
use Modules\AuditPro\Support\Maturity;

class AuditController extends Controller
{
    public function index(Request $request, DefaultTemplateProvisioner $provisioner): View
    {
        $provisioner->provision($request->user()->currentTeam);

        $audits = Audit::with(['template', 'creator', 'results'])
            ->latest()
            ->take(8)
            ->get();
        $templates = AuditTemplate::withCount('questions')->orderByDesc('is_default')->orderBy('name')->get();

        $stats = [
            'total' => Audit::count(),
            'active' => Audit::where('status', 'in_progress')->count(),
            'completed' => Audit::where('status', 'completed')->count(),
            'average' => round((float) AuditResult::avg('average_score'), 2),
        ];

        return view('auditpro::index', compact('audits', 'templates', 'stats'));
    }

    public function start(Request $request): RedirectResponse
    {
        $team = $request->user()->currentTeam;
        $validated = $request->validate([
            'template_id' => [
                'required',
                Rule::exists('auditpro_templates', 'id')->where('team_id', $team->id),
            ],
            'audit_type' => 'required|in:major,small,challenge,kpi_check',
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'company_age' => 'nullable|integer|min:0|max:250',
        ]);

        $auditType = $validated['audit_type'];
        $lastCompletedAt = Audit::where('team_id', $team->id)
            ->where('audit_type', $auditType)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->value('completed_at');

        $cooldownDays = match ($auditType) {
            'major' => 180,
            'small' => 90,
            'challenge' => 28,
            'kpi_check' => 7,
            default => 180,
        };

        if ($lastCompletedAt && $lastCompletedAt->diffInDays(now()) < $cooldownDays) {
            $nextAt = $lastCompletedAt->clone()->addDays($cooldownDays)->format('Y-m-d');

            return back()->with('error', __('A :type audit can be taken once every :days days. Next possible date: :date.', [
                'type' => __($auditType),
                'days' => $cooldownDays,
                'date' => $nextAt,
            ]));
        }

        $audit = Audit::create([
            'team_id' => $team->id,
            'template_id' => $validated['template_id'],
            'created_by' => $request->user()->id,
            'status' => 'in_progress',
            'audit_type' => $auditType,
            'company_name' => $validated['company_name'] ?: ($team->company_name ?: $team->name),
            'industry' => $validated['industry'] ?: $team->industry,
            'size' => $validated['size'] ?: $team->size,
            'company_age' => $validated['company_age'] ?? $team->company_age,
        ]);

        return redirect()->route('audit.assessment', $audit);
    }

    public function results(Audit $audit): View
    {
        abort_unless($audit->status === 'completed', 404);

        $audit->load(['team', 'template.pillars', 'results', 'creator']);
        $overallScore = (float) ($audit->results->avg('average_score') ?? 0);
        $overallMaturity = Maturity::label($overallScore);
        $radarLabels = $audit->results->pluck('level')->values();
        $radarScores = $audit->results->pluck('average_score')->map(fn ($score) => (float) $score)->values();
        $allocoreScore = AllocoreScoreService::latestForTeam($audit->team_id);
        $recommendations = app(AllocoreRecommendationService::class)->forScore($allocoreScore, Auth::user());
        $benchmark = $allocoreScore ? AllocoreBenchmarkService::percentile($allocoreScore) : null;
        $industryStats = $allocoreScore && $allocoreScore->industry ? AllocoreBenchmarkService::industryStats($allocoreScore->industry) : null;

        return view('auditpro::results', compact(
            'audit',
            'overallScore',
            'overallMaturity',
            'radarLabels',
            'radarScores',
            'allocoreScore',
            'recommendations',
            'benchmark',
            'industryStats',
        ));
    }

    public function report(Audit $audit): View
    {
        abort_unless($audit->status === 'completed', 404);

        $audit->load(['team', 'template.pillars', 'results', 'creator']);
        $overallScore = (float) ($audit->results->avg('average_score') ?? 0);
        $overallMaturity = Maturity::label($overallScore);

        return view('auditpro::report', compact('audit', 'overallScore', 'overallMaturity'));
    }

    public function downloadReport(Audit $audit, AuditPdfService $pdfService)
    {
        abort_unless($audit->status === 'completed', 404);

        return $pdfService->download($audit);
    }

    public function destroy(Request $request, Audit $audit): RedirectResponse
    {
        $canDelete = $audit->created_by === $request->user()->id
            || $request->user()->currentTeam->owner_id === $request->user()->id;

        abort_unless($canDelete, 403);

        $audit->delete();

        return back()->with('success', __('Audit deleted.'));
    }
}
