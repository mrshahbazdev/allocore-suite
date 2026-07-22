<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWebhookCallJob;
use App\Models\Integration;
use App\Models\Webhook;
use App\Models\WebhookCall;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function create(Request $request)
    {
        $integration = Integration::findOrFail($request->integration_id);

        return view('admin.webhooks.create', compact('integration'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'integration_id' => 'required|exists:integrations,id',
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:1000',
            'events' => 'nullable|string|max:1000',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        Webhook::create([
            'integration_id' => $validated['integration_id'],
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_filter(array_map('trim', explode(',', $validated['events'] ?? ''))),
            'secret' => $validated['secret'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.integrations.edit', $validated['integration_id'])->with('success', __('admin.webhooks.created'));
    }

    public function edit(Webhook $webhook)
    {
        return view('admin.webhooks.edit', compact('webhook'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:1000',
            'events' => 'nullable|string|max:1000',
            'secret' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $webhook->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_filter(array_map('trim', explode(',', $validated['events'] ?? ''))),
            'secret' => $validated['secret'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.integrations.edit', $webhook->integration_id)->with('success', __('admin.webhooks.updated'));
    }

    public function destroy(Webhook $webhook)
    {
        $integrationId = $webhook->integration_id;
        $webhook->delete();

        return redirect()->route('admin.integrations.edit', $integrationId)->with('success', __('admin.webhooks.deleted'));
    }

    public function history(Webhook $webhook)
    {
        $calls = $webhook->calls()->latest()->paginate(20);

        return view('admin.webhooks.history', compact('webhook', 'calls'));
    }

    public function retry(WebhookCall $webhookCall)
    {
        $webhookCall->update([
            'failed_at' => null,
            'failure_message' => null,
            'response_status' => null,
            'response_body' => null,
        ]);

        SendWebhookCallJob::dispatch($webhookCall);

        return redirect()->route('admin.webhooks.history', $webhookCall->webhook_id)->with('success', __('Webhook call retried.'));
    }
}
