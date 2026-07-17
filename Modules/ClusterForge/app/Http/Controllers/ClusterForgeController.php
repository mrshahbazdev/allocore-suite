<?php

namespace Modules\ClusterForge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ClusterForge\Models\KeywordCluster;
use Modules\ClusterForge\Services\KeywordClusterService;

class ClusterForgeController extends Controller
{
    public function index(): View
    {
        $clusters = KeywordCluster::latest()->paginate(10);

        return view('clusterforge::index', compact('clusters'));
    }

    public function store(Request $request, KeywordClusterService $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'keywords' => 'required|string',
            'is_public' => 'nullable|boolean',
        ]);

        $keywords = array_filter(preg_split('/[\r\n,]+/', $validated['keywords']));

        if (count($keywords) < 2) {
            return back()->with('error', __('Enter at least two keywords.'));
        }

        $clusters = $service->cluster($keywords);

        $cluster = KeywordCluster::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'keywords' => array_values($keywords),
            'clusters' => $clusters,
            'is_public' => $validated['is_public'] ?? false,
            'public_slug' => ($validated['is_public'] ?? false) ? (string) Str::ulid() : null,
        ]);

        return redirect()->route('clusterforge.show', $cluster)->with('success', __('Keyword clusters generated.'));
    }

    public function show(KeywordCluster $cluster): View
    {
        return view('clusterforge::show', compact('cluster'));
    }

    public function destroy(KeywordCluster $cluster): RedirectResponse
    {
        $cluster->delete();

        return redirect()->route('clusterforge.index')->with('success', __('Cluster deleted.'));
    }
}
