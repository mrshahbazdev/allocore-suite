<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['currentTeam', 'teams', 'roles', 'toolSubscriptions.plan'])
            ->latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function role(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        $user->syncRoles($validated['role']);

        return back()->with('success', __('User role updated.'));
    }
}
