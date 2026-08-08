<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['currentTeam', 'teams', 'roles', 'toolSubscriptions.plan'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('email', 'like', '%'.$request->search.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $teams = Team::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('teams', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => ['required', Password::defaults()],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'current_team_id' => 'nullable|exists:teams,id',
            'email_verified' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'locale' => 'nullable|string|in:en,de',
        ]);

        $verified = $validated['email_verified'] ?? false;
        unset($validated['email_verified']);

        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        $user = User::create($validated);
        $user->syncRoles($roles);

        if ($verified) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('admin.users.index')->with('success', __('admin.users.created'));
    }

    public function show(User $user)
    {
        $user->load(['currentTeam', 'teams', 'roles', 'toolSubscriptions.plan.modules']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles');
        $teams = Team::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'teams', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', Password::defaults()],
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'current_team_id' => 'nullable|exists:teams,id',
            'email_verified' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'locale' => 'nullable|string|in:en,de',
        ]);

        $verified = $validated['email_verified'] ?? false;
        unset($validated['email_verified']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        $user->update($validated);
        $user->syncRoles($roles);

        if ($verified && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('admin.users.index')->with('success', __('admin.users.updated'));
    }

    public function role(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->syncRoles($validated['role']);

        return back()->with('success', __('admin.users.role_updated'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('admin.users.cannot_delete_self'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('admin.users.deleted'));
    }
}
