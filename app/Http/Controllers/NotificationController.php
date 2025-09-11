<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Fetch unread notifications
    public function fetch()
    {
        $customer = auth()->user()->customer;

        $notifications = $customer->unreadNotifications->map(function($n) {
            return [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'admin_reply' => $n->data['admin_reply'] ?? null,
                'time' => $n->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'count' => $customer->unreadNotifications->count(),
            'notifications' => $notifications
        ]);
    }

    // Mark all unread notifications as read
    public function markAsRead(Request $request)
    {
        $notifications = auth()->user()->customer->notifications;
        auth()->user()->customer->unreadNotifications->markAsRead();

        return view('notifications.index', compact('notifications'));
    }

    // // Optional: View all notifications
    // public function index()
    // {
    //     $customer = auth()->user()->customer;
    //     $notifications = $customer->notifications()->latest()->get();

    //     return view('notifications.index', compact('notifications'));
    // }
}
