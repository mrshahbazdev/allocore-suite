<?php

namespace Modules\DevManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\DevManager\Models\Integration;

class IntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integration::with('idea')->latest()->paginate(25);

        return view('devmanager::integrations.index', compact('integrations'));
    }

    public function store(Request $request)
    {
        $data = $this->validateIntegration($request);

        Integration::create($data);

        return redirect()->route('devmanager.integrations.index')->with('message', __('Integration saved.'));
    }

    public function update(Request $request, Integration $integration)
    {
        $data = $this->validateIntegration($request);

        $integration->update($data);

        return redirect()->route('devmanager.integrations.index')->with('message', __('Integration updated.'));
    }

    public function destroy(Integration $integration)
    {
        $integration->delete();

        return redirect()->route('devmanager.integrations.index')->with('message', __('Integration deleted.'));
    }

    protected function validateIntegration(Request $request): array
    {
        return $request->validate([
            'provider' => ['required', 'in:github,azure_devops,jira,clickup'],
            'idea_id' => ['nullable', 'exists:devmanager_ideas,id'],
            'config.url' => ['nullable', 'url'],
            'config.project' => ['nullable', 'string', 'max:255'],
            'config.token' => ['nullable', 'string', 'max:500'],
            'config.space' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
