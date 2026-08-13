<?php

namespace Modules\AuditPro\Http\Controllers;

use App\Models\AllocoreScore;
use App\Models\Industry;
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
use Modules\AuditPro\Services\PillarTemplateProvisioner;
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
        $industryClusters = Industry::clusters()->get();

        $stats = [
            'total' => Audit::count(),
            'active' => Audit::where('status', 'in_progress')->count(),
            'completed' => Audit::where('status', 'completed')->count(),
            'average' => round((float) AuditResult::avg('average_score'), 2),
        ];

        return view('auditpro::index', compact('audits', 'templates', 'stats', 'industryClusters'));
    }

    public function start(Request $request): RedirectResponse
    {
        return $this->createAudit($request, $request->input('audit_type', 'major'), null);
    }

    public function startSmall(Request $request, PillarTemplateProvisioner $provisioner): RedirectResponse
    {
        $focusPillar = $request->input('focus_pillar');

        if (! in_array($focusPillar, ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy'], true)) {
            return back()->with('error', __('Please select a valid pillar.'));
        }

        $template = $provisioner->provision($request->user()->currentTeam, $focusPillar);

        return $this->createAudit($request, 'small', $focusPillar, $template->id);
    }

    private function createAudit(Request $request, string $auditType, ?string $focusPillar, ?int $templateId = null): RedirectResponse
    {
        $team = $request->user()->currentTeam;

        $validated = $request->validate([
            'template_id' => [
                'nullable',
                Rule::exists('auditpro_templates', 'id')->where('team_id', $team->id),
            ],
            'company_name' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'industry_sub' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'company_age' => 'nullable|integer|min:0|max:250',
        ]);

        if (! $templateId) {
            $templateId = $validated['template_id'];
        }

        $cooldownQuery = Audit::where('team_id', $team->id)
            ->where('audit_type', $auditType)
            ->where('status', 'completed');

        if ($focusPillar) {
            $cooldownQuery->where('focus_pillar', $focusPillar);
        }

        $lastCompletedAt = $cooldownQuery->latest('completed_at')->value('completed_at');

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
            'template_id' => $templateId,
            'created_by' => $request->user()->id,
            'status' => 'in_progress',
            'audit_type' => $auditType,
            'focus_pillar' => $focusPillar,
            'company_name' => ($validated['company_name'] ?? null) ?: ($team->company_name ?: $team->name),
            'industry' => ($validated['industry'] ?? null) ?: $team->industry,
            'industry_sub' => ($validated['industry_sub'] ?? null) ?: $team->industry_sub,
            'size' => ($validated['size'] ?? null) ?: $team->size,
            'company_age' => $validated['company_age'] ?? $team->company_age,
        ]);

        if ($audit->audit_type === 'major') {
            $team->update([
                'company_name' => $audit->company_name,
                'industry' => $audit->industry,
                'industry_sub' => $audit->industry_sub,
                'size' => $audit->size,
                'company_age' => $audit->company_age,
            ]);
        }

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

        if ($audit->audit_type === 'major') {
            $allocoreScore = AllocoreScoreService::latestForTeam($audit->team_id);
        } else {
            $allocoreScore = AllocoreScore::make([
                'team_id' => $audit->team_id,
                'company_name' => $audit->company_name ?: ($audit->team->company_name ?: $audit->team->name),
                'industry' => $audit->industry ?: $audit->team->industry,
                'industry_sub' => $audit->industry_sub ?: $audit->team->industry_sub,
                'size' => $audit->size ?: $audit->team->size,
                'company_age' => $audit->company_age ?? $audit->team->company_age,
                'score' => round(($overallScore / 4) * 100, 2),
                'maturity_level' => $overallMaturity,
                'pillars' => $audit->results->map(fn ($r) => [
                    'name' => $r->level,
                    'score' => round(((float) $r->average_score / 4) * 100, 2),
                    'maturity' => $r->maturity_level,
                ])->values()->all(),
            ]);
        }

        $recommendations = app(AllocoreRecommendationService::class)->forScore($allocoreScore, Auth::user());
        $benchmark = $allocoreScore && $allocoreScore->industry ? AllocoreBenchmarkService::percentile($allocoreScore) : null;
        $industryStats = $allocoreScore && $allocoreScore->industry ? AllocoreBenchmarkService::industryStats($allocoreScore->industry, $allocoreScore->industry_sub) : null;

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
