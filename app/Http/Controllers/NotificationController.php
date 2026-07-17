<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(DatabaseNotification $notification)
    {
        abort_if($notification->notifiable_id !== auth()->id(), 403);

        $notification->markAsRead();

        return back()->with('success', __('Notification marked as read.'));
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', __('All notifications marked as read.'));
    }
}
