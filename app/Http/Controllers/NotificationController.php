<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{

    public function index(): View
    {
        if (!Schema::hasTable('notifications')) {
            return view('notifications.index', ['notifications' => collect()]);
        }

        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        auth()->user()->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    /** AJAX: unread count for topbar bell */
    public function unreadCount()
    {
        if (!Schema::hasTable('notifications')) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /** Mark specific notification as read */
    public function markRead(string $id): RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        auth()->user()->notifications()->find($id)?->markAsRead();
        return back();
    }

    /** Mark all as read */
    public function markAllRead(): RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            return back()->with('success', 'Notifications are not enabled yet.');
        }

        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    /** Delete notification */
    public function destroy(string $id): RedirectResponse
    {
        if (!Schema::hasTable('notifications')) {
            return back();
        }

        auth()->user()->notifications()->find($id)?->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
