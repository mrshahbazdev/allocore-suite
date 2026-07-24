<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleImporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

class ImportController extends Controller
{
    public function __construct(protected ModuleImporter $importer) {}

    public function index(Request $request)
    {
        $modules = $this->importer->supportedModules();

        return view('imports.index', compact('modules'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'module_key' => ['required', 'string', Rule::in(Module::where('is_active', true)->pluck('key')->all())],
            'file' => ['required', 'file', 'mimes:csv,xlsx', 'max:5120'],
        ]);

        $path = $request->file('file')->store('imports');
        $realPath = storage_path('app/'.$path);

        $module = Module::where('key', $validated['module_key'])->firstOrFail();
        $headers = $this->importer->headers($realPath);
        $preview = $this->importer->preview($realPath);
        $columns = $this->importer->importableColumns($module->key);

        session()->put('import.path', $realPath);
        session()->put('import.module_key', $module->key);

        return view('imports.map', compact('module', 'headers', 'preview', 'columns', 'path'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mapping' => ['required', 'array'],
            'mapping.*' => ['nullable', 'string'],
        ]);

        $path = session('import.path');
        $moduleKey = session('import.module_key');

        if (! $path || ! $moduleKey || ! file_exists($path)) {
            return redirect()->route('imports.index')->with('error', __('Import session expired. Please upload again.'));
        }

        $result = $this->importer->import($moduleKey, $path, $request->user(), $validated['mapping']);

        if (file_exists($path)) {
            unlink($path);
        }

        session()->forget(['import.path', 'import.module_key']);

        return redirect()->route('imports.index')->with([
            'success' => __('Import completed. :created records created.', ['created' => $result['created']]),
            'import_errors' => $result['errors'],
        ]);
    }
}
