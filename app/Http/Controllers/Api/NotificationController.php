<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications($email)
    {
        $user = User::where('email', $email)->first();

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
                    'date' => Carbon::parse($item->date)->translatedFormat('d-m-Y'),
                    'status' => $item->status,
                ];
            })
        ]);
    }
    public function getNotificationsAdmin()
    {

        $notifications = Notification::where('id_admin', 1)->latest()->with('user')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications->map(function ($item) {
                // Menentukan pesan berdasarkan status
                $message = match ($item->status) {
                    'register' => $item->user->user_name . ' baru bergabung dengan bank sampah',
                    'pending' => $item->user->user_name . ' mengajukan penarikan saldo',
                    'deposit' => $item->user->user_name . ' menyetor sampah',
                    'verified' => 'Pengajuan penarikan saldo ' . $item->user->user_name . ' disetujui oleh admin',
                    'rejected' => 'Pengajuan penarikan saldo ' . $item->user->user_name . ' ditolak oleh admin',
                    default => $item->message_content, // fallback ke message_content asli jika status tidak dikenali
                };
                return [
                    'message' => $message,
                    'date' => Carbon::parse($item->date)->translatedFormat('d-m-Y'),
                    'status' => $item->status,
                ];
            })
        ]);
    }
}
