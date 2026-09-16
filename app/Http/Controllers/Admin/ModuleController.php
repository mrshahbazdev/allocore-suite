<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as ModuleFacade;
use Spatie\Permission\Models\Role;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $installed = Module::all()->keyBy('key');
        $diskModules = collect(ModuleFacade::all())->map(function ($module) use ($installed) {
            $key = Str::kebab($module->getName());
            $record = $installed->get($key);

            if (! $record) {
                $record = Module::firstOrCreate(
                    ['key' => $key],
                    [
                        'name' => $module->getName(),
                        'description' => $module->get('description', ''),
                        'icon' => 'sparkles',
                        'category' => 'Produktivität & Prozesse',
                        'route_prefix' => $module->get('alias', $key),
                        'in_subscription_pool' => true,
                        'is_deprecated' => false,
                        'is_active' => true,
                    ]
                );
            }

            return [
                'name' => $module->getName(),
                'alias' => $module->get('alias', $key),
                'key' => $key,
                'description' => $module->get('description', ''),
                'path' => $module->getPath(),
                'record' => $record,
            ];
        })->sortBy(function ($item) {
            return $item['record'] ? $item['record']->sort_order : 999;
        })->values();

        $roles = Role::orderBy('name')->get();

        $categories = [
            'Finanzen',
            'Vertrieb & Marketing',
            'Produktivität & Prozesse',
            'Führung & Strategie',
            'Wissen & Bildung',
        ];

        return view('admin.modules', compact('diskModules', 'roles', 'categories'));
    }

    public function install(string $name)
    {
        $module = ModuleFacade::find($name);

        if (! $module) {
            return back()->with('error', __('Module not found.'));
        }

        $key = Str::kebab($module->getName());
        $record = Module::where('key', $key)->first();

        if (! $record) {
            Module::create([
                'key' => $key,
                'name' => $module->getName(),
                'description' => $module->get('description', ''),
                'icon' => 'sparkles',
                'category' => 'Produktivität & Prozesse',
                'route_prefix' => $module->get('alias', $key),
                'in_subscription_pool' => true,
                'is_deprecated' => false,
                'is_active' => true,
            ]);
        } else {
            $record->update(['is_active' => true]);
        }

        try {
            Artisan::call('module:migrate', ['module' => $name, '--force' => true]);
        } catch (\Throwable $e) {
            return back()->with('error', __('Migration failed: :message', ['message' => $e->getMessage()]));
        }

        try {
            Artisan::call('module:seed', ['module' => $name, '--force' => true]);
        } catch (\Throwable $e) {
            // Seeders are optional for many modules.
        }

        $this->syncAllToolsPlan();

        return back()->with('success', __('Module installed and activated.'));
    }

    public function update(Request $request, Module $module)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'badge_text' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'route_prefix' => 'nullable|string|max:255',
            'allowed_roles' => 'nullable|array',
            'allowed_roles.*' => 'exists:roles,name',
            'is_active' => 'nullable|boolean',
            'in_subscription_pool' => 'nullable|boolean',
            'is_deprecated' => 'nullable|boolean',
        ]);

        $module->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'category' => $validated['category'] ?? ($module->category ?: 'Produktivität & Prozesse'),
            'icon' => $validated['icon'] ?? ($module->icon ?: 'sparkles'),
            'badge_text' => ! empty($validated['badge_text']) ? $validated['badge_text'] : null,
            'sort_order' => isset($validated['sort_order']) ? (int) $validated['sort_order'] : $module->sort_order,
            'route_prefix' => ! empty($validated['route_prefix']) ? $validated['route_prefix'] : $module->route_prefix,
            'allowed_roles' => $validated['allowed_roles'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $module->is_active,
            'in_subscription_pool' => $request->has('in_subscription_pool') ? $request->boolean('in_subscription_pool') : $module->in_subscription_pool,
            'is_deprecated' => $request->has('is_deprecated') ? $request->boolean('is_deprecated') : $module->is_deprecated,
        ]);

        $this->syncAllToolsPlan();

        return back()->with('success', __('Tool ":name" updated successfully.', ['name' => $module->name]));
    }

    public function toggle(Module $module)
    {
        $module->update(['is_active' => ! $module->is_active]);
        $this->syncAllToolsPlan();

        return back()->with('success', $module->is_active ? __('Module activated.') : __('Module deactivated.'));
    }

    public function togglePool(Module $module)
    {
        $module->update(['in_subscription_pool' => ! $module->in_subscription_pool]);
        $this->syncAllToolsPlan();

        return back()->with('success', $module->in_subscription_pool
            ? __('Tool added to the subscription tool pool.')
            : __('Tool removed from the subscription tool pool.'));
    }

    public function toggleDeprecate(Module $module)
    {
        $module->update(['is_deprecated' => ! $module->is_deprecated]);
        $this->syncAllToolsPlan();

        return back()->with('success', $module->is_deprecated
            ? __('Tool marked as deprecated/archived.')
            : __('Tool restored from archive.'));
    }

    protected function syncAllToolsPlan(): void
    {
        try {
            $allToolsPlan = Plan::where('slug', 'all-tools')->first();
            if ($allToolsPlan) {
                $poolModuleIds = Module::where('in_subscription_pool', true)
                    ->where('is_active', true)
                    ->where('is_deprecated', false)
                    ->pluck('id')
                    ->all();
                $allToolsPlan->modules()->sync($poolModuleIds);
            }
        } catch (\Throwable $e) {
            // Non-critical background sync
        }
    }
}
