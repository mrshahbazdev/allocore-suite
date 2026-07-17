<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class BulkUserController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'selected' => 'required|array',
            'selected.*' => 'integer|exists:users,id',
            'action' => 'required|in:delete,activate,deactivate,verify',
        ]);

        $users = User::whereIn('id', $validated['selected']);

        switch ($validated['action']) {
            case 'delete':
                $users->get()->each->delete();
                break;
            case 'activate':
                $users->update(['is_active' => true]);
                break;
            case 'deactivate':
                $users->update(['is_active' => false]);
                break;
            case 'verify':
                $users->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
                break;
        }

        $key = match ($validated['action']) {
            'delete' => 'deleted',
            'verify' => 'verified',
            default => 'updated',
        };

        return redirect()->route('admin.users.index')->with('success', __('admin.bulk_users.'.$key));
    }
}
