<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    // ... (metode lain seperti getSavingsDetails, dll. tetap ada)

    public function getSavingsDetails($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_balance' => number_format($user->balance, 2, ',', '.') . ' Rp',
                'account_count' => 1, // Asumsi 1 rekening per user untuk saat ini
                'username' => $user->username,
                'balance_per_account' => number_format($user->balance, 2, ',', '.') . ' Rp',
                'is_primary' => $user->is_primary,
            ]
        ]);
    }

    public function createWithdrawal(Request $request, $username)
    {
        // Cari pengguna berdasarkan username
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        // Validasi data
        $validator = Validator::make($request->all(), [
            'amount' => [
                'required',
                'numeric',
                'min:1000', // Minimum penarikan, misalnya Rp1.000
                function ($attribute, $value, $fail) use ($user) {
                    if ($value > $user->balance) {
                        $fail('Jumlah penarikan melebihi saldo Anda (Rp. ' . number_format($user->balance, 2, ',', '.') . ').');
                    }
                    if ($value <= 0) {
                        $fail('Jumlah penarikan harus lebih dari Rp0.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Buat entri penarikan (saldo belum dikurangi sampai disetujui)
        $withdrawal = Withdrawal::create([
            'id_user' => $user->id_user,
            'user_name' => $user->user_name,
            'withdrawal_date' => now()->toDateString(),
            'withdrawal_amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $withdrawal->id_withdrawal,
                'user_name' => $withdrawal->user_name,
                'amount' => number_format($withdrawal->withdrawal_amount, 2, ',', '.') . ' Rp',
                'date' => $withdrawal->withdrawal_date,
                'status' => $withdrawal->status,
            ]
        ], 201);
    }

    public function verifyWithdrawal(Request $request, $id)
    {
        // Cari penarikan berdasarkan ID
        $withdrawal = Withdrawal::find($id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal not found'
            ], 404);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:setuju,tolak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Ambil ID admin yang sedang login (asumsi autentikasi sudah diatur)
        $adminId = Auth::guard('admin')->id(); // Sesuaikan dengan guard yang digunakan

        // Perbarui status berdasarkan aksi
        $status = $request->action === 'setuju' ? 'approved' : 'rejected';
        $withdrawal->update([
            'status' => $status,
            'admin_verified_by' => $adminId,
        ]);

        // Jika disetujui, kurangi saldo pengguna
        if ($status === 'approved') {
            $user = $withdrawal->user;
            $user->balance -= $withdrawal->withdrawal_amount;
            $user->withdrawal_count += 1;
            $user->withdrawal_amount += $withdrawal->withdrawal_amount;
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $withdrawal->id_withdrawal,
                'user_name' => $withdrawal->user_name,
                'amount' => number_format($withdrawal->withdrawal_amount, 2, ',', '.') . ' Rp',
                'date' => $withdrawal->withdrawal_date,
                'status' => $withdrawal->status,
                'verified_by' => $withdrawal->admin_verified_by,
            ]
        ]);
    }

    // Metode opsional untuk mengambil daftar penarikan yang perlu diverifikasi
    public function getPendingWithdrawals()
    {
        $withdrawals = Withdrawal::where('status', 'pending')->get();

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals->map(function ($item) {
                return [
                    'id' => $item->id_withdrawal,
                    'user_name' => $item->user_name,
                    'amount' => number_format($item->withdrawal_amount, 2, ',', '.') . ' Rp',
                    'date' => $item->withdrawal_date,
                    'status' => $item->status,
                ];
            })
        ]);
    }
}
