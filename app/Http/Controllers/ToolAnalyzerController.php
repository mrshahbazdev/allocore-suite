<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleGate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ToolAnalyzerController extends Controller
{
    public function __invoke(Request $request, ModuleGate $gate): View
    {
        $user = $request->user();
        $modules = Module::where('is_active', true)->get();
        $active = $modules->filter(fn ($m) => $user->hasModule($m->key))->values();
        $locked = $modules->filter(fn ($m) => ! $user->hasModule($m->key))->values();

        $recommendations = [
            ['key' => 'invoice-maker', 'reason' => __('Send invoices and track payments.')],
            ['key' => 'lead-quality', 'reason' => __('Capture leads, score them and run outreach.')],
            ['key' => 'audit', 'reason' => __('Run business maturity audits with reports.')],
            ['key' => 'kpi-tool', 'reason' => __('Track KPIs with targets and spreadsheets.')],
            ['key' => 'financial-platform', 'reason' => __('Deep financial KPIs and revenue development.')],
            ['key' => 'plan-hive', 'reason' => __('Manage projects, tasks and goals.')],
        ];

        $suggested = collect($recommendations)
            ->filter(fn ($r) => ! $user->hasModule($r['key']))
            ->map(fn ($r) => $r + ['module' => $modules->firstWhere('key', $r['key'])])
            ->filter(fn ($r) => $r['module'])
            ->values();

        $deepKpiMissing = $gate->missingFor($user, $gate->requiredForAnalysis('deep-kpis'));

        return view('tool-analyzer.index', compact('modules', 'active', 'locked', 'suggested', 'deepKpiMissing'));
    }
}
