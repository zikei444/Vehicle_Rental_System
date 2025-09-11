<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Fetch unread notifications
    public function fetch()
    {
        $customer = auth()->user()->customer;

        $notifications = $customer->unreadNotifications->map(function ($n) {
            return [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'admin_reply' => $n->data['admin_reply'] ?? null,
                'time' => $n->created_at->diffForHumans()
            ];
        });

        // Mark as read immediately so they show only once
        $customer->unreadNotifications->markAsRead();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function fetchAdmin()
    {
        $admin = auth()->user();

        $notifications = $admin->unreadNotifications->map(function ($n) {
            return [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'time' => $n->created_at->diffForHumans()
            ];
        });

        $admin->unreadNotifications->markAsRead();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id)
{
    $notification = auth()->user()->notifications()->find($id);
    if ($notification) {
        $notification->markAsRead();
    }
    return response()->json(['success' => true]);
}
}
