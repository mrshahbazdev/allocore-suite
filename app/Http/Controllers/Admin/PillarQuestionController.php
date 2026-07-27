<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\AuditPro\Models\PillarQuestionBlueprint;
use Modules\AuditPro\Services\PillarTemplateProvisioner;

class PillarQuestionController extends Controller
{
    protected array $pillars = ['Revenue', 'Profit', 'Order', 'Influence', 'Legacy'];

    public function index(): View
    {
        $counts = PillarQuestionBlueprint::selectRaw('pillar, count(*) as total')
            ->groupBy('pillar')
            ->pluck('total', 'pillar');

        return view('admin.auditpro.pillar-questions.index', [
            'pillars' => $this->pillars,
            'counts' => $counts,
        ]);
    }

    public function edit(string $pillar, PillarTemplateProvisioner $provisioner): View
    {
        if (! in_array($pillar, $this->pillars, true)) {
            abort(404);
        }

        $provisioner->seedDefaults($pillar);

        $groups = PillarQuestionBlueprint::forPillar($pillar)
            ->mains()
            ->orderBy('position')
            ->with('children')
            ->get()
            ->map(function ($main) {
                return [
                    'main' => $main,
                    'follow_ups' => $main->children,
                ];
            });

        return view('admin.auditpro.pillar-questions.edit', [
            'pillar' => $pillar,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, string $pillar): RedirectResponse
    {
        if (! in_array($pillar, $this->pillars, true)) {
            abort(404);
        }

        $validated = $request->validate([
            'groups' => 'required|array|min:1',
            'groups.*.main.question' => 'required|string|max:5000',
            'groups.*.main.description' => 'nullable|string|max:5000',
            'groups.*.main.recommendation' => 'nullable|string|max:5000',
            'groups.*.follow_ups' => 'array',
            'groups.*.follow_ups.*.question' => 'required|string|max:5000',
            'groups.*.follow_ups.*.description' => 'nullable|string|max:5000',
            'groups.*.follow_ups.*.recommendation' => 'nullable|string|max:5000',
        ]);

        PillarQuestionBlueprint::where('pillar', $pillar)->delete();

        foreach ($validated['groups'] as $groupIndex => $groupData) {
            $mainPosition = ($groupIndex + 1) * 10;

            $main = PillarQuestionBlueprint::create([
                'pillar' => $pillar,
                'parent_id' => null,
                'position' => $mainPosition,
                'question' => $groupData['main']['question'],
                'description' => $groupData['main']['description'] ?? null,
                'recommendation' => $groupData['main']['recommendation'] ?? null,
                'is_active' => true,
            ]);

            foreach ($groupData['follow_ups'] ?? [] as $followUpIndex => $followUp) {
                PillarQuestionBlueprint::create([
                    'pillar' => $pillar,
                    'parent_id' => $main->id,
                    'position' => $mainPosition + $followUpIndex + 1,
                    'question' => $followUp['question'],
                    'description' => $followUp['description'] ?? null,
                    'recommendation' => $followUp['recommendation'] ?? null,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.audits.pillar-questions.edit', $pillar)
            ->with('success', __(':pillar mini-audit question groups updated.', ['pillar' => $pillar]));
    }
}
