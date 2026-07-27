<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module as ModuleFacade;

class ModuleController extends Controller
{
    public function index()
    {
        $installed = Module::all()->keyBy('key');
        $diskModules = collect(ModuleFacade::all())->map(function ($module) use ($installed) {
            $key = Str::kebab($module->getName());
            $record = $installed->get($key);

            return [
                'name' => $module->getName(),
                'alias' => $module->get('alias', $key),
                'key' => $key,
                'description' => $module->get('description', ''),
                'path' => $module->getPath(),
                'record' => $record,
            ];
        });

        return view('admin.modules', compact('diskModules'));
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
                'route_prefix' => $module->get('alias', $key),
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

        return back()->with('success', __('Module installed and activated.'));
    }

    public function update(Request $request, Module $module)
    {
        $module->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('Module updated.'));
    }

    public function toggle(Module $module)
    {
        $module->update(['is_active' => ! $module->is_active]);

        return back()->with('success', $module->is_active ? __('Module activated.') : __('Module deactivated.'));
    }
}
