<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $integrations = Integration::withCount('webhooks')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->search.'%')->orWhere('type', 'like', '%'.$request->search.'%');
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.integrations.index', compact('integrations'));
    }

    public function create()
    {
        return view('admin.integrations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['config'] = $this->normalizeConfig($validated['config'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        Integration::create($validated);

        return redirect()->route('admin.integrations.index')->with('success', __('admin.integrations.created'));
    }

    public function edit(Integration $integration)
    {
        $integration->load('webhooks');

        return view('admin.integrations.edit', compact('integration'));
    }

    public function update(Request $request, Integration $integration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'config' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['config'] = $this->normalizeConfig($validated['config'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        $integration->update($validated);

        return redirect()->route('admin.integrations.index')->with('success', __('admin.integrations.updated'));
    }

    public function destroy(Integration $integration)
    {
        $integration->delete();

        return redirect()->route('admin.integrations.index')->with('success', __('admin.integrations.deleted'));
    }

    private function normalizeConfig(array $config): array
    {
        return collect($config)
            ->mapWithKeys(fn ($item) => [($item['key'] ?? '') => $item['value'] ?? ''])
            ->filter(fn ($value, $key) => filled($key))
            ->toArray();
    }
}
