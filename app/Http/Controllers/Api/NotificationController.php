<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $notifications = Notification::where('id_user', $user->id_user)->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications->map(function ($item) {
                return [
                    'message' => $item->message_content,
                    'date' => $item->date,
                    'status' => $item->status,
                    'admin_verified' => $item->id_admin ? 'verified by admin' : ($item->status === 'rejected' ? 'Ditolak' : 'pending'),
                ];
            })
        ]);
    }
}
