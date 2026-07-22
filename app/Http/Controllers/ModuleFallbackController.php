<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModuleFallbackController extends Controller
{
    public function __invoke(Request $request, string $prefix)
    {
        $module = Module::where('route_prefix', $prefix)->firstOrFail();
        abort_unless($request->user()->hasModule($module->key), 403);

        return view('modules.placeholder', compact('module'));
    }
}
