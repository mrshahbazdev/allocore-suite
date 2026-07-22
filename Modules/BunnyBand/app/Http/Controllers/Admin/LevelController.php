<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\Level;

class LevelController extends Controller
{
    public function index(): View
    {
        $levels = Level::orderBy('sort_order')->orderBy('created_at')->paginate(20);

        return view('bunnyband::admin.levels.index', compact('levels'));
    }

    public function create(): View
    {
        return view('bunnyband::admin.levels.form', ['level' => new Level]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Level::create($validated);

        return redirect()->route('bunnyband.admin.levels.index')->with('success', __('Level created.'));
    }

    public function edit(Level $level): View
    {
        return view('bunnyband::admin.levels.form', compact('level'));
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $level->update($validated);

        return redirect()->route('bunnyband.admin.levels.index')->with('success', __('Level updated.'));
    }

    public function destroy(Level $level): RedirectResponse
    {
        $level->delete();

        return back()->with('success', __('Level deleted.'));
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'type' => 'required|in:free,paid',
            'price' => 'nullable|numeric|min:0',
            'daily_earning_limit' => 'nullable|numeric|min:0',
            'referral_bonus' => 'nullable|numeric|min:0',
            'task_bonus_percent' => 'nullable|numeric|min:0|max:100',
            'withdrawal_limit' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ];
    }
}
