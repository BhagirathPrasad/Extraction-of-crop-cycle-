<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        // Mark unread ones as read when viewing the full list
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * AJAX: poll for latest unread count and notifications list.
     */
    public function poll(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'count' => 0,
                'notifications' => []
            ], 401);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $recent = $user->notifications()->latest()->take(5)->get();

        $formatted = $recent->map(function ($notif) {
            $data = $notif->data;
            return [
                'id' => $notif->id,
                'title' => $data['title'] ?? 'Notification',
                'message' => $data['message'] ?? 'A new system event has been logged.',
                'url' => $data['url'] ?? '#',
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'info',
                'read_at' => $notif->read_at ? $notif->read_at->toIso8601String() : null,
                'created_at' => $notif->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $formatted
        ]);
    }

    /**
     * AJAX: unread count for topbar bell
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark specific notification as read.
     */
    public function markRead(string $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $notification->delete();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }
}
