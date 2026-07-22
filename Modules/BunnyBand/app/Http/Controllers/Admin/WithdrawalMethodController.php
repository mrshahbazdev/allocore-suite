<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\WithdrawalMethod;

class WithdrawalMethodController extends Controller
{
    public function index(): View
    {
        $methods = WithdrawalMethod::orderByDesc('created_at')->paginate(20);

        return view('bunnyband::admin.withdrawal-methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('bunnyband::admin.withdrawal-methods.form', ['method' => new WithdrawalMethod]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        WithdrawalMethod::create($validated);

        return redirect()->route('bunnyband.admin.withdrawal-methods.index')->with('success', __('Method created.'));
    }

    public function edit(WithdrawalMethod $withdrawalMethod): View
    {
        return view('bunnyband::admin.withdrawal-methods.form', ['method' => $withdrawalMethod]);
    }

    public function update(Request $request, WithdrawalMethod $withdrawalMethod): RedirectResponse
    {
        $validated = $this->validated($request);

        $withdrawalMethod->update($validated);

        return redirect()->route('bunnyband.admin.withdrawal-methods.index')->with('success', __('Method updated.'));
    }

    public function destroy(WithdrawalMethod $withdrawalMethod): RedirectResponse
    {
        $withdrawalMethod->delete();

        return back()->with('success', __('Method deleted.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
