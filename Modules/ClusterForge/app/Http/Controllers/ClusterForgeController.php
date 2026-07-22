<?php

namespace Modules\ClusterForge\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\ClusterForge\Jobs\ClusterKeywordsJob;
use Modules\ClusterForge\Models\KeywordCluster;
use Modules\ClusterForge\Services\KeywordClusterService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClusterForgeController extends Controller
{
    public function index(Request $request): View
    {
        $query = KeywordCluster::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $clusters = $query->paginate(15)->withQueryString();

        return view('clusterforge::index', compact('clusters'));
    }

    public function store(Request $request, KeywordClusterService $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywords' => 'nullable|string',
            'keywords_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'tags' => 'nullable|string',
            'algorithm' => 'required|in:terms,similarity',
            'is_public' => 'nullable|boolean',
        ]);

        $keywords = $this->resolveKeywords($request, $validated);

        if (count($keywords) < 2) {
            return back()->with('error', __('Enter at least two keywords.'));
        }

        $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags'] ?? ''))));

        $cluster = KeywordCluster::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tags' => $tags,
            'keywords' => $keywords,
            'clusters' => [],
            'algorithm' => $validated['algorithm'],
            'status' => 'processing',
            'is_public' => $validated['is_public'] ?? false,
            'public_slug' => ($validated['is_public'] ?? false) ? (string) Str::ulid() : null,
        ]);

        ClusterKeywordsJob::dispatch($cluster);

        return redirect()->route('clusterforge.show', $cluster)->with('success', __('Clustering queued.'));
    }

    public function show(KeywordCluster $cluster): View
    {
        return view('clusterforge::show', compact('cluster'));
    }

    public function edit(KeywordCluster $cluster): View
    {
        return view('clusterforge::edit', compact('cluster'));
    }

    public function update(Request $request, KeywordCluster $cluster, KeywordClusterService $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'tags' => 'nullable|string',
            'algorithm' => 'required|in:terms,similarity',
            'is_public' => 'nullable|boolean',
            'keywords' => 'nullable|string',
        ]);

        $tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags'] ?? ''))));

        $cluster->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'tags' => $tags,
            'algorithm' => $validated['algorithm'],
            'is_public' => $validated['is_public'] ?? false,
            'public_slug' => ($validated['is_public'] ?? false) ? ($cluster->public_slug ?? (string) Str::ulid()) : null,
        ]);

        if (! empty($validated['keywords'])) {
            $keywords = $this->parseKeywords($validated['keywords']);
            if (count($keywords) >= 2) {
                $cluster->update([
                    'keywords' => $keywords,
                    'status' => 'processing',
                ]);
                ClusterKeywordsJob::dispatch($cluster);
            }
        }

        return redirect()->route('clusterforge.show', $cluster)->with('success', __('Cluster updated.'));
    }

    public function destroy(KeywordCluster $cluster): RedirectResponse
    {
        $cluster->delete();

        return redirect()->route('clusterforge.index')->with('success', __('Cluster deleted.'));
    }

    public function export(KeywordCluster $cluster): StreamedResponse
    {
        $fileName = Str::slug($cluster->name, '_').'_clusters.csv';

        return new StreamedResponse(function () use ($cluster) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [__('Cluster'), __('Keyword')]);

            foreach ($cluster->clusters ?? [] as $name => $keywords) {
                foreach ($keywords as $keyword) {
                    fputcsv($handle, [$name, $keyword]);
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function publicShow(string $publicSlug): View
    {
        $cluster = KeywordCluster::public()->where('public_slug', $publicSlug)->firstOrFail();

        return view('clusterforge::public', compact('cluster'));
    }

    protected function resolveKeywords(Request $request, array $validated): array
    {
        if ($request->hasFile('keywords_file')) {
            $path = $request->file('keywords_file')->getRealPath();
            $content = file_get_contents($path) ?: '';

            return $this->parseKeywords($content);
        }

        return $this->parseKeywords($validated['keywords'] ?? '');
    }

    protected function parseKeywords(string $input): array
    {
        $raw = preg_split('/[\r\n,]+/', $input);

        return array_values(array_unique(array_filter(array_map('trim', $raw ?? []))));
    }
}
