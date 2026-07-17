<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    public function index(Request $request)
    {
        $templates = NotificationTemplate::when($request->filled('search'), function ($query) use ($request) {
            $query->where('key', 'like', '%'.$request->search.'%')
                ->orWhere('subject', 'like', '%'.$request->search.'%');
        })->latest()->paginate(20)->withQueryString();

        return view('admin.notification-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.notification-templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'type' => 'required|in:email,in_app',
            'subject' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['variables'] = $this->parseVariables($validated['variables'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        NotificationTemplate::create($validated);

        return redirect()->route('admin.notification-templates.index')->with('success', __('admin.notification_templates.created'));
    }

    public function edit(NotificationTemplate $notificationTemplate)
    {
        return view('admin.notification-templates.edit', compact('notificationTemplate'));
    }

    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'type' => 'required|in:email,in_app',
            'subject' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['variables'] = $this->parseVariables($validated['variables'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        $notificationTemplate->update($validated);

        return redirect()->route('admin.notification-templates.index')->with('success', __('admin.notification_templates.updated'));
    }

    public function destroy(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->delete();

        return redirect()->route('admin.notification-templates.index')->with('success', __('admin.notification_templates.deleted'));
    }

    private function parseVariables(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
