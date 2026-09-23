<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Models\LearningPath;
use Modules\BookIntelligence\Services\PersonalizedLearningPathGenerator;

class LearningPathController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $learningPaths = LearningPath::query()->where('user_id', $user->id)->with('targetRole')->latest()->get();
        $roles = CompetencyRole::query()->orderBy('name')->get();

        return view('bookintelligence::learning.index', compact('learningPaths', 'roles'));
    }

    public function generate(Request $request, PersonalizedLearningPathGenerator $generator): RedirectResponse
    {
        @set_time_limit(300);
        $validated = $request->validate([
            'target_role_id' => ['required', 'exists:bookintelligence_competency_roles,id'],
            'current_role_id' => ['nullable', 'exists:bookintelligence_competency_roles,id'],
        ]);

        $targetRole = CompetencyRole::findOrFail($validated['target_role_id']);
        $currentRole = !empty($validated['current_role_id']) ? CompetencyRole::find($validated['current_role_id']) : null;

        try {
            $path = $generator->generateForUser(auth()->user(), $targetRole, $currentRole);

            return redirect()->route('bookintelligence.learning.show', $path->id)
                ->with('status', 'Personalized AI Learning Path created successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to generate learning path: ' . $e->getMessage()]);
        }
    }

    public function show(LearningPath $learningPath): View
    {
        $learningPath->loadMissing('targetRole');

        return view('bookintelligence::learning.show', compact('learningPath'));
    }

    public function updateStep(Request $request, LearningPath $learningPath): RedirectResponse
    {
        $stepIndex = (int) $request->input('step_index');
        $steps = $learningPath->steps ?? [];

        if (isset($steps[$stepIndex])) {
            $steps[$stepIndex]['status'] = 'completed';
            $steps[$stepIndex]['completed_at'] = now()->toDateString();

            // Set next step in_progress
            if (isset($steps[$stepIndex + 1]) && $steps[$stepIndex + 1]['status'] === 'pending') {
                $steps[$stepIndex + 1]['status'] = 'in_progress';
            }

            $completedCount = collect($steps)->where('status', 'completed')->count();
            $progressPercent = (int) round(($completedCount / count($steps)) * 100);

            $learningPath->update([
                'steps' => $steps,
                'progress_percent' => $progressPercent,
                'status' => $progressPercent >= 100 ? LearningPath::STATUS_COMPLETED : LearningPath::STATUS_ACTIVE,
            ]);
        }

        return redirect()->back()->with('status', 'Step marked as complete!');
    }
}
