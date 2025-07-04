<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\News;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getDashboardData($username)
    {
        // Find user by username
        $user = User::where('username', $username)->first();

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
                'greeting' => 'Halo, ' . $user->user_name,
                'profile' => [
                    'photo' => $user->photo,
                ],
                'balance' => [
                    'amount' => $user->balance,
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
}
