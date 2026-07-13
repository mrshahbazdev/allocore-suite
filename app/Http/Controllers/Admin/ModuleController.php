<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::with('plans')->orderBy('name')->get();

        return view('admin.modules', compact('modules'));
    }

    public function update(Request $request, Module $module)
    {
        $module->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('Module updated.'));
    }
}
