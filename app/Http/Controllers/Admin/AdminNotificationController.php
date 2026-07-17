<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = DatabaseNotification::with('notifiable')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('data', 'like', '%'.$request->search.'%');
            })
            ->latest()
            ->paginate(25);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        $users = User::orderBy('name')->pluck('name', 'id');

        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|in:all,selected',
            'user_ids' => 'required_if:recipient,selected|array',
            'user_ids.*' => 'integer|exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'type' => 'required|in:info,success,warning,danger',
            'action_url' => 'nullable|url|max:1000',
            'action_text' => 'nullable|string|max:100',
        ]);

        $query = User::query();
        if ($validated['recipient'] === 'selected') {
            $query->whereIn('id', $validated['user_ids'] ?? []);
        }

        $query->where('is_active', true)->chunkById(100, function ($users) use ($validated) {
            $notification = new GeneralNotification(
                $validated['subject'],
                $validated['body'],
                $validated['action_url'],
                $validated['action_text'],
                $validated['type']
            );

            foreach ($users as $user) {
                $user->notify($notification);
            }
        });

        return redirect()->route('admin.notifications.index')->with('success', __('admin.notifications.sent'));
    }

    public function destroy(DatabaseNotification $notification)
    {
        $notification->delete();

        return redirect()->route('admin.notifications.index')->with('success', __('admin.notifications.deleted'));
    }
}
