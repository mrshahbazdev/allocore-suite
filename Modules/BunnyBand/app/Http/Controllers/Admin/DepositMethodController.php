<?php

namespace Modules\BunnyBand\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\BunnyBand\Models\DepositMethod;

class DepositMethodController extends Controller
{
    public function index(): View
    {
        $methods = DepositMethod::orderByDesc('created_at')->paginate(20);

        return view('bunnyband::admin.deposit-methods.index', compact('methods'));
    }

    public function create(): View
    {
        return view('bunnyband::admin.deposit-methods.form', ['method' => new DepositMethod]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DepositMethod::create($validated);

        return redirect()->route('bunnyband.admin.deposit-methods.index')->with('success', __('Method created.'));
    }

    public function edit(DepositMethod $depositMethod): View
    {
        return view('bunnyband::admin.deposit-methods.form', ['method' => $depositMethod]);
    }

    public function update(Request $request, DepositMethod $depositMethod): RedirectResponse
    {
        $validated = $this->validated($request);

        $depositMethod->update($validated);

        return redirect()->route('bunnyband.admin.deposit-methods.index')->with('success', __('Method updated.'));
    }

    public function destroy(DepositMethod $depositMethod): RedirectResponse
    {
        $depositMethod->delete();

        return back()->with('success', __('Method deleted.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
