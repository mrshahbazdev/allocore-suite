<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    private array $siteFields = ['site_name', 'support_email', 'default_locale'];

    private array $appearanceFields = ['site_logo', 'site_favicon', 'primary_color', 'accent_color', 'footer_text'];

    public function index(Request $request)
    {
        $step = (int) $request->input('step', 0);
        $modules = Module::orderBy('name')->get();
        $settings = [];

        foreach (array_merge($this->siteFields, $this->appearanceFields) as $field) {
            $settings[$field] = SiteSetting::value($field, $this->defaultFor($field));
        }

        return view('admin.setup.index', compact('step', 'modules', 'settings'));
    }

    public function storeSite(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'support_email' => 'nullable|email|max:255',
            'default_locale' => 'required|in:'.implode(',', config('app.available_locales', ['en'])),
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return redirect()->route('admin.setup.index', ['step' => 1]);
    }

    public function storeModules(Request $request)
    {
        $validated = $request->validate([
            'modules' => 'nullable|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $activeIds = $validated['modules'] ?? [];
        Module::query()->update(['is_active' => false]);
        Module::whereIn('id', $activeIds)->update(['is_active' => true]);

        return redirect()->route('admin.setup.index', ['step' => 2]);
    }

    public function storeAppearance(Request $request)
    {
        $validated = $request->validate([
            'site_logo' => 'nullable|string|max:1000',
            'site_favicon' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:20',
            'accent_color' => 'nullable|string|max:20',
            'footer_text' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::set($key, $value);
        }

        return redirect()->route('admin.setup.index', ['step' => 3]);
    }

    public function complete()
    {
        SiteSetting::setGlobal('setup_wizard_completed', true);

        return redirect()->route('onboarding.index')->with('success', __('Setup completed. Continue creating your team.'));
    }

    private function defaultFor(string $field): mixed
    {
        return match ($field) {
            'default_locale' => config('app.locale', 'en'),
            'primary_color' => '#ff9200',
            'accent_color' => '#0094af',
            default => '',
        };
    }
}
