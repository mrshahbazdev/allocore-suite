<?php

namespace Modules\LoopEngine\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\LoopEngine\Models\Webhook;

class WebhookController extends Controller
{
    public function index(): View
    {
        $webhooks = Webhook::where('created_by', auth()->id())->latest()->paginate(15);

        return view('loopengine::webhooks.index', compact('webhooks'));
    }

    public function create(): View
    {
        return view('loopengine::webhooks.form', ['webhook' => new Webhook, 'events' => self::events()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->rules($request);
        $validated['events'] = $request->input('events', []);
        $validated['created_by'] = auth()->id();

        Webhook::create($validated);

        return redirect()->route('loopengine.webhooks.index')->with('success', __('Webhook created.'));
    }

    public function edit(Webhook $webhook): View
    {
        return view('loopengine::webhooks.form', compact('webhook'));
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $validated = $this->rules($request);
        $validated['events'] = $request->input('events', []);

        $webhook->update($validated);

        return redirect()->route('loopengine.webhooks.index')->with('success', __('Webhook updated.'));
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $webhook->delete();

        return redirect()->route('loopengine.webhooks.index')->with('success', __('Webhook deleted.'));
    }

    public function logs(Webhook $webhook): View
    {
        $logs = $webhook->logs()->latest()->paginate(20);

        return view('loopengine::webhooks.logs', compact('webhook', 'logs'));
    }

    private function rules(Request $request): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'secret' => 'nullable|string',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public static function events(): array
    {
        return [
            'run.started',
            'run.completed',
            'run.paused',
            'run.resumed',
            'run.cancelled',
            'run.looped_back',
            'process.activated',
            'process.archived',
            'assignment.created',
        ];
    }
}
