<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Support\ModuleMarketplace;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MarketplaceController extends Controller
{
    public function index(Request $request, ModuleMarketplace $marketplace)
    {
        $user = $request->user();
        $grouped = $marketplace->grouped();

        return view('marketplace.index', compact('user', 'grouped'));
    }

    public function show(Request $request, Module $module, ModuleMarketplace $marketplace)
    {
        $details = $marketplace->forModule($module->key);

        abort_if(! $details, 404);

        return view('marketplace.show', compact('module', 'details'));
    }
}
