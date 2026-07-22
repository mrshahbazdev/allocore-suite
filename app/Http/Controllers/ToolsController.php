<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ToolsController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->orderBy('name')->get();
        $accessible = $modules->filter(fn ($module) => $user->hasModule($module->key))->values();
        $locked = $modules->filter(fn ($module) => ! $user->hasModule($module->key))->values();

        return view('tools.index', compact('accessible', 'locked'));
    }
}
