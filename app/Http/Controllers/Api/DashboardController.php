<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\News;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardData($email)
    {
        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Get latest news
        $news = News::latest()->take(1)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'greeting' => $user->user_name,
                'profile' => [
                    'photo' => $user->photo,
                ],
                'balance' => [
                    'amount' => number_format($user->balance, 2, ',', '.'),
                    'currency' => 'Rp.'
                ],
                'news' => $news->map(function ($item) {
                    return [
                        'title' => $item->title,
                        'content' => $item->content,
                        'photo' => $item->photo,
                        'date' => $item->date
                    ];
                })
            ]
        ]);
    }
    public function getDashboardDataAdmin($email)
    {
        // Find user by email
        $user = Admin::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Get latest news
        $news = News::latest()->take(1)->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'greeting' => $user->admin_name,
                'profile' => [
                    'photo' => $user->photo,
                ],
                'news' => $news->map(function ($item) {
                    return [
                        'title' => $item->title,
                        'content' => $item->content,
                        'photo' => $item->photo,
                        'date' => $item->date
                    ];
                })
            ]
        ]);
    }
}
