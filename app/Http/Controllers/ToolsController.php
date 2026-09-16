<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Support\ModuleRecommender;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ToolsController extends Controller
{
    public function __invoke(Request $request, ModuleRecommender $recommender)
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)
            ->where('is_deprecated', false)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $accessible = $modules->filter(fn ($module) => $user->hasModule($module->key))->values();
        $locked = $modules->filter(fn ($module) => ! $user->hasModule($module->key))->values();
        
        $categorizedAccessible = $accessible->groupBy(fn ($m) => $m->category ?: 'Allgemein');
        $categorizedLocked = $locked->groupBy(fn ($m) => $m->category ?: 'Allgemein');
        
        $recommendations = $recommender->forUser($user);

        return view('tools.index', compact('accessible', 'locked', 'categorizedAccessible', 'categorizedLocked', 'recommendations'));
    }
}
