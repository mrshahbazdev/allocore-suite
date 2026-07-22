<?php

namespace Modules\NurDu\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\NurDu\Models\GuidingPrinciple;
use Modules\NurDu\Models\Vision;

class VisionController extends Controller
{
    public function index(Request $request): View
    {
        $vision = Vision::where('team_id', $request->user()->current_team_id)
            ->with('guidingPrinciples')
            ->first();

        return view('nurdu::vision.index', compact('vision'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'statement' => ['required', 'string', 'max:500'],
        ]);

        $user = $request->user();

        Vision::updateOrCreate(
            ['team_id' => $user->current_team_id],
            ['user_id' => $user->id, 'statement' => $validated['statement']]
        );

        return redirect()->route('nurdu.vision.index')->with('success', __('Vision statement saved.'));
    }

    public function storePrinciple(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $vision = Vision::where('team_id', $request->user()->current_team_id)->firstOrFail();
        $maxOrder = $vision->guidingPrinciples()->max('sort_order') ?? 0;

        $vision->guidingPrinciples()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('nurdu.vision.index')->with('success', __('Guiding principle added.'));
    }

    public function updatePrinciple(Request $request, GuidingPrinciple $principle): RedirectResponse
    {
        $vision = Vision::where('team_id', $request->user()->current_team_id)->firstOrFail();
        abort_if($principle->vision_id !== $vision->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $principle->update($validated);

        return redirect()->route('nurdu.vision.index')->with('success', __('Guiding principle updated.'));
    }

    public function destroyPrinciple(Request $request, GuidingPrinciple $principle): RedirectResponse
    {
        $vision = Vision::where('team_id', $request->user()->current_team_id)->firstOrFail();
        abort_if($principle->vision_id !== $vision->id, 403);

        $principle->delete();

        return redirect()->route('nurdu.vision.index')->with('success', __('Guiding principle removed.'));
    }
}
