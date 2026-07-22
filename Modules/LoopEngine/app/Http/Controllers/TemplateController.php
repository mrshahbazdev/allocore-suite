<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Process;
use Modules\LoopEngine\Models\ProcessTemplate;
use Modules\LoopEngine\Models\TemplateRating;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProcessTemplate::with('process', 'sharedBy')->where('is_public', true);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request): void {
                $q->where('name_en', 'like', '%'.$request->search.'%')
                    ->orWhere('name_de', 'like', '%'.$request->search.'%');
            });
        }

        $templates = $query->orderBy('install_count', 'desc')->paginate(15)->withQueryString();
        $categories = ProcessTemplate::distinct()->pluck('category');

        return view('loopengine::templates.index', compact('templates', 'categories'));
    }

    public function show(ProcessTemplate $template): View
    {
        $template->load('ratings.user', 'process');

        return view('loopengine::templates.show', compact('template'));
    }

    public function share(Request $request, Process $process): RedirectResponse
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_de' => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'description_de' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        $validated['tags'] = array_filter(array_map('trim', explode(',', $validated['tags'] ?? '')));
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['team_id'] = $process->team_id;
        $validated['process_id'] = $process->id;
        $validated['shared_by'] = auth()->id();

        ProcessTemplate::create($validated);

        return redirect()->route('loopengine.templates.index')->with('success', __('Process shared.'));
    }

    public function install(ProcessTemplate $template): RedirectResponse
    {
        $clone = $template->process->duplicate(auth()->user());

        $template->increment('install_count');

        return redirect()->route('loopengine.processes.edit', $clone)->with('success', __('Template installed.'));
    }

    public function rate(Request $request, ProcessTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $rating = TemplateRating::updateOrCreate(
            ['template_id' => $template->id, 'user_id' => auth()->id()],
            $validated + ['team_id' => auth()->user()->current_team_id]
        );

        $template->recalculateRating();

        return redirect()->route('loopengine.templates.show', $template)->with('success', __('Rating saved.'));
    }

    public function destroy(ProcessTemplate $template): RedirectResponse
    {
        $template->delete();

        return redirect()->route('loopengine.templates.index')->with('success', __('Template deleted.'));
    }
}
