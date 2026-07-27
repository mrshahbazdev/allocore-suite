<?php

namespace Modules\AuditPro\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\AuditPro\Models\Audit;
use Modules\AuditPro\Models\AuditChallenge;
use Modules\AuditPro\Services\AuditChallengeService;

class ChallengeController extends Controller
{
    public function __construct(private AuditChallengeService $challengeService) {}

    public function index(Request $request): View
    {
        $challenges = AuditChallenge::with('audit')
            ->where('team_id', $request->user()->current_team_id)
            ->latest()
            ->paginate(20);

        return view('auditpro::challenges.index', compact('challenges'));
    }

    public function show(AuditChallenge $challenge): View
    {
        abort_unless($challenge->team_id === auth()->user()->current_team_id, 403);

        return view('auditpro::challenges.show', compact('challenge'));
    }

    public function store(Request $request, Audit $audit): RedirectResponse
    {
        $user = $request->user();

        abort_unless($audit->team_id === $user->current_team_id, 403);
        abort_unless($audit->status === 'completed', 403);
        abort_unless($audit->audit_type === 'small', 403);

        $pillar = $audit->focus_pillar ?? $audit->results->first()?->level;

        if (! $pillar) {
            return back()->with('error', __('This audit has no pillar to base a challenge on.'));
        }

        if (! $this->challengeService->canStartForTeam($user->current_team_id, $pillar)) {
            return back()->with('error', __('An active or recently completed challenge already exists for :pillar.', ['pillar' => $pillar]));
        }

        $challenge = $this->challengeService->createFromAudit($audit, $user);

        return redirect()->route('audit.challenges.show', $challenge)
            ->with('success', __('Challenge started for :pillar.', ['pillar' => $pillar]));
    }

    public function toggleStep(Request $request, AuditChallenge $challenge): RedirectResponse
    {
        abort_unless($challenge->team_id === $request->user()->current_team_id, 403);

        $validated = $request->validate([
            'step_id' => 'required|string',
            'completed' => 'required|boolean',
        ]);

        $this->challengeService->toggleStep($challenge, $validated['step_id'], (bool) $validated['completed']);

        return back()->with('success', __('Progress updated.'));
    }
}
