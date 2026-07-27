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

        $questions = PillarQuestionBlueprint::forPillar($pillar)
            ->orderBy('position')
            ->get();

        return view('admin.auditpro.pillar-questions.edit', [
            'pillar' => $pillar,
            'questions' => $questions,
        ]);
    }

    public function update(Request $request, string $pillar): RedirectResponse
    {
        if (! in_array($pillar, $this->pillars, true)) {
            abort(404);
        }

        $validated = $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:5000',
            'questions.*.description' => 'nullable|string|max:5000',
            'questions.*.recommendation' => 'nullable|string|max:5000',
        ]);

        PillarQuestionBlueprint::where('pillar', $pillar)->delete();

        foreach ($validated['questions'] as $index => $data) {
            PillarQuestionBlueprint::create([
                'pillar' => $pillar,
                'position' => $index + 1,
                'question' => $data['question'],
                'description' => $data['description'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.pillar-questions.edit', $pillar)
            ->with('success', __(':pillar mini-audit questions updated.', ['pillar' => $pillar]));
    }
}
