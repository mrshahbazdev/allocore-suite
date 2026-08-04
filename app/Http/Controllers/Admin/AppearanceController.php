<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    private array $fields = [
        'site_name',
        'site_logo',
        'site_favicon',
        'primary_color',
        'accent_color',
        'font_family',
        'footer_text',
        'public_nav_menu',
        'social_links',
        'dashboard_template',
    ];

    public function index()
    {
        $settings = [];

        foreach ($this->fields as $field) {
            $settings[$field] = SiteSetting::value($field, $this->defaultFor($field));
        }

        return view('admin.appearance.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate($this->rules());

        foreach ($validated as $key => $value) {
            if (in_array($key, ['public_nav_menu', 'social_links'], true)) {
                $value = $this->normalizeLinks($value);
            }
            SiteSetting::set($key, $value);
        }

        return back()->with('success', __('Appearance settings updated.'));
    }

    public function customize(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:modules,id',
            'is_active' => 'boolean',
        ]);

        $module = Module::findOrFail($validated['module_id']);
        $module->update(['is_active' => $request->boolean('is_active', $module->is_active)]);

        return back()->with('success', __('Module status updated.'));
    }

    private function rules(): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            $rules[$field] = in_array($field, ['public_nav_menu', 'social_links'], true) ? 'nullable|array' : 'nullable|string|max:2000';
        }

        return $rules;
    }

    private function defaultFor(string $field): mixed
    {
        return match ($field) {
            'primary_color' => '#ff9200',
            'accent_color' => '#0094af',
            'font_family' => 'figtree',
            'public_nav_menu' => [],
            'social_links' => [],
            'dashboard_template' => 'default',
            default => '',
        };
    }

    private function normalizeLinks(array $value): array
    {
        return collect($value)
            ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))
            ->values()
            ->all();
    }
}
