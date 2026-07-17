<?php

namespace Modules\AuditPro\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
        $teamId = $request->user()->current_team_id;
        $validated = $request->validate([
            'template_id' => [
                'required',
                Rule::exists('auditpro_templates', 'id')->where('team_id', $teamId),
            ],
        ]);

        $audit = Audit::create([
            'team_id' => $teamId,
            'template_id' => $validated['template_id'],
            'created_by' => $request->user()->id,
            'status' => 'in_progress',
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

        return view('auditpro::results', compact(
            'audit',
            'overallScore',
            'overallMaturity',
            'radarLabels',
            'radarScores',
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
