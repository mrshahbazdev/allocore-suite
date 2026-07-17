<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function impersonate(Request $request, User $user)
    {
        $admin = $request->user();

        if (! session()->has('impersonated_by')) {
            session(['impersonated_by' => $admin->id]);
        }

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard')->with('success', __('admin.impersonation.started', ['name' => $user->name]));
    }

    public function stop()
    {
        $adminId = session('impersonated_by');

        if ($adminId) {
            Auth::loginUsingId($adminId);
            session()->forget(['impersonated_by', 'admin_url_intended']);
        }

        return redirect()->route('admin.users.index')->with('success', __('admin.impersonation.stopped'));
    }
}
