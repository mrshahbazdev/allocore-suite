<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionManagerController extends Controller
{
    public function index(Request $request)
    {
        $sessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->orderByDesc('last_activity')
            ->paginate(25);

        $userIds = $sessions->pluck('user_id')->unique()->filter()->all();
        $users = User::whereIn('id', $userIds)->pluck('email', 'id');

        return view('admin.session-manager.index', compact('sessions', 'users'));
    }

    public function destroy(string $id)
    {
        DB::table('sessions')->where('id', $id)->delete();

        return redirect()->route('admin.session-manager.index')->with('success', __('admin.session_manager.deleted'));
    }
}
