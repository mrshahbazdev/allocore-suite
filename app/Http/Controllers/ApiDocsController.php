<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Support\ModuleStats;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiDocsController extends Controller
{
    public function __invoke(Request $request)
    {
        $modules = Module::where('is_active', true)
            ->get()
            ->filter(fn ($module) => app(ModuleStats::class)->modelFor($module->key))
            ->values();

        return view('api-docs.index', compact('modules'));
    }
}
