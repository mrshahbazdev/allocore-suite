<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    private array $tools = [
        'core' => 'Core',
        'invoicemaker' => 'InvoiceMaker',
        'planhive' => 'PlanHive',
        'focusmatrix' => 'FocusMatrix',
        'financialplatform' => 'FinancialPlatform',
    ];

    public function index(Request $request)
    {
        $templates = NotificationTemplate::where('type', 'email')
            ->when($request->filled('tool'), function ($query) use ($request) {
                $query->where('tool', $request->tool);
            })
            ->orderBy('tool')
            ->orderBy('key')
            ->orderBy('locale')
            ->get()
            ->groupBy('tool');

        return view('admin.email-templates.index', [
            'grouped' => $templates,
            'tools' => $this->tools,
            'currentTool' => $request->get('tool'),
        ]);
    }

    public function edit(NotificationTemplate $template)
    {
        return view('admin.email-templates.edit', [
            'template' => $template,
            'tools' => $this->tools,
        ]);
    }

    public function update(Request $request, NotificationTemplate $template)
    {
        $validated = $request->validate([
            'tool' => 'required|string|max:50',
            'key' => 'required|string|max:255',
            'locale' => 'required|string|max:10',
            'subject' => 'nullable|string|max:1000',
            'body' => 'required|string',
            'variables' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['variables'] = $this->parseVariables($validated['variables'] ?? null);
        $validated['is_active'] = $request->boolean('is_active', true);

        $template->update($validated);

        return redirect()->route('admin.email-templates.index', ['tool' => $template->tool])
            ->with('success', __('admin.email_templates.updated'));
    }

    public function preview(NotificationTemplate $template)
    {
        $rendered = app(EmailTemplateService::class)->render(
            $template->tool ?? '',
            $template->key,
            $this->sampleVariables($template->variables ?? []),
            $template->locale
        );

        return response()->json($rendered);
    }

    private function parseVariables(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    private function sampleVariables(array $variables): array
    {
        $samples = [];

        foreach ($variables as $variable) {
            $samples[$variable] = ucfirst(str_replace('_', ' ', $variable));
        }

        return $samples;
    }
}
