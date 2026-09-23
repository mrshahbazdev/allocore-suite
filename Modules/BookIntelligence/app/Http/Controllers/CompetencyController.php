<?php

namespace Modules\BookIntelligence\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BookIntelligence\Models\CareerPath;
use Modules\BookIntelligence\Models\CompetencyRole;
use Modules\BookIntelligence\Services\CompetencyFrameworkService;

class CompetencyController extends Controller
{
    public function index(CompetencyFrameworkService $service): View
    {
        $teamId = auth()->user()?->currentTeam?->id ?? 1;
        $roles = $service->ensureDefaultRoles($teamId);
        $careerPaths = CareerPath::query()->with(['fromRole', 'toRole'])->get();

        return view('bookintelligence::competency.index', compact('roles', 'careerPaths'));
    }

    public function showRole(CompetencyRole $role): View
    {
        $role->loadMissing(['nextRole', 'careerPathsFrom.toRole', 'careerPathsTo.fromRole', 'assessments', 'challenges']);
        $allRoles = CompetencyRole::query()->where('id', '!=', $role->id)->orderBy('name')->get();

        return view('bookintelligence::competency.role-show', compact('role', 'allRoles'));
    }

    public function storeRole(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:64'],
            'level' => ['required', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'required_knowledge' => ['nullable', 'string'],
            'required_competencies' => ['nullable', 'string'],
            'required_skills' => ['nullable', 'string'],
        ]);

        $teamId = auth()->user()?->currentTeam?->id ?? 1;

        $parseList = fn (?string $text) => collect(preg_split('/[\r\n,]+/', (string) $text))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        CompetencyRole::create([
            'team_id' => $teamId,
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']) . '-' . \Illuminate\Support\Str::random(4),
            'department' => $validated['department'],
            'level' => $validated['level'],
            'description' => $validated['description'] ?? null,
            'required_knowledge' => $parseList($validated['required_knowledge'] ?? null),
            'required_competencies' => $parseList($validated['required_competencies'] ?? null),
            'required_skills' => $parseList($validated['required_skills'] ?? null),
        ]);

        return redirect()->route('bookintelligence.competency.index')
            ->with('status', __("Rolle ':name' erfolgreich hinzugefügt!", ['name' => $validated['name']]));
    }

    public function updateRole(Request $request, CompetencyRole $role): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:64'],
            'level' => ['required', 'string', 'max:32'],
            'description' => ['nullable', 'string'],
            'required_knowledge' => ['nullable', 'string'],
            'required_competencies' => ['nullable', 'string'],
            'required_skills' => ['nullable', 'string'],
            'next_role_id' => ['nullable', 'exists:bookintelligence_competency_roles,id'],
        ]);

        $parseList = fn (?string $text) => collect(preg_split('/[\r\n,]+/', (string) $text))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $role->update([
            'name' => $validated['name'],
            'department' => $validated['department'],
            'level' => $validated['level'],
            'description' => $validated['description'] ?? null,
            'required_knowledge' => $parseList($validated['required_knowledge'] ?? null),
            'required_competencies' => $parseList($validated['required_competencies'] ?? null),
            'required_skills' => $parseList($validated['required_skills'] ?? null),
            'next_role_id' => $validated['next_role_id'] ?? null,
        ]);

        return redirect()->route('bookintelligence.competency.roles.show', $role->id)
            ->with('status', __('Rolle erfolgreich aktualisiert!'));
    }

    public function destroyRole(CompetencyRole $role): \Illuminate\Http\RedirectResponse
    {
        $name = $role->name;
        $role->delete();

        return redirect()->route('bookintelligence.competency.index')
            ->with('status', __("Rolle ':name' wurde entfernt.", ['name' => $name]));
    }
}
