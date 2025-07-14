<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankBalance;
use App\Models\User;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $bank = User::all();
        return response()->json([
            'status' => 'success',
            'data' => $bank->map(function ($item) {
                return [
                    'username' => $item->username,
                    'balance_per_account' => 'Rp. ' . number_format($item->balance, 2, ',', '.'),
                ];
            })
        ]);
    }
}
