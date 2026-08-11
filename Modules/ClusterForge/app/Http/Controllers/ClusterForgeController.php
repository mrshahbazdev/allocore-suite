<?php

namespace Modules\ClusterForge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ClusterForge\Http\Requests\StoreProjectRequest;
use Modules\ClusterForge\Jobs\GenerateProjectJob;
use Modules\ClusterForge\Models\Project;
use Modules\ClusterForge\Services\GeminiService;

class ClusterForgeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $projects = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'processing' => Project::where('team_id', auth()->user()->current_team_id)->processing()->count(),
            'completed' => Project::where('team_id', auth()->user()->current_team_id)->completed()->count(),
            'failed' => Project::where('team_id', auth()->user()->current_team_id)->failed()->count(),
        ];

        return view('clusterforge::index', [
            'projects' => $projects,
            'stats' => $stats,
            'geminiConfigured' => (new GeminiService)->isConfigured(),
        ]);
    }

    public function create(): View
    {
        return view('clusterforge::create', [
            'geminiConfigured' => (new GeminiService)->isConfigured(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $supported = (array) config('app.supported_locales', ['en', 'de']);
        $locale = App::getLocale();
        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        $project = Project::create([
            'user_id' => $request->user()->id,
            'topic' => $request->string('topic'),
            'website' => $request->string('website'),
            'language' => $locale,
            'status' => Project::STATUS_PENDING,
        ]);

        GenerateProjectJob::dispatch($project->id);

        return redirect()->route('clusterforge.show', $project)->with('success', __('Cluster generation queued.'));
    }

    public function show(Project $project): View
    {
        $project->load(['subtopics.questions']);

        return view('clusterforge::show', [
            'project' => $project,
            'geminiConfigured' => (new GeminiService)->isConfigured(),
        ]);
    }

    public function status(Request $request, Project $project)
    {
        return response()->json([
            'id' => $project->id,
            'status' => $project->status,
            'status_label' => $project->statusLabel(),
            'progress_percent' => $project->progressPercent(),
            'is_in_progress' => $project->isInProgress(),
            'error' => $project->error,
        ]);
    }

    public function retry(Request $request, Project $project): RedirectResponse
    {
        $project->update([
            'status' => Project::STATUS_PENDING,
            'error' => null,
        ]);

        GenerateProjectJob::dispatch($project->id);

        return redirect()->route('clusterforge.show', $project);
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('clusterforge.index')->with('success', __('Project deleted.'));
    }

    public function exportPillar(Request $request, Project $project): Response
    {
        $filename = sprintf('pillar-%s.md', Str::slug($project->topic ?: 'page'));
        $body = sprintf(
            "<!--\nTitle: %s\nMeta Description: %s\n-->\n\n%s\n",
            $project->pillar_title ?? '',
            $project->pillar_meta_description ?? '',
            $project->pillar_content ?? ''
        );

        return response($body, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportCluster(Request $request, Project $project, int $subtopic): Response
    {
        $sub = $project->subtopics()->findOrFail($subtopic);

        $filename = sprintf(
            'cluster-%s.md',
            Str::slug($sub->long_tail_keyword ?: $sub->title ?: 'page')
        );

        $body = sprintf(
            "<!--\nTitle: %s\nMeta Description: %s\nLong-tail keyword: %s\n-->\n\n%s\n",
            $sub->cluster_title ?? '',
            $sub->cluster_meta_description ?? '',
            $sub->long_tail_keyword ?? '',
            $sub->cluster_content ?? ''
        );

        return response($body, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
