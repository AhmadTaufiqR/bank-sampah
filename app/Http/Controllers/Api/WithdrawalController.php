<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\BankBalance;
use App\Models\Notification;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    // ... (metode lain seperti getSavingsDetails, dll. tetap ada)

    public function getSavingsDetails($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_balance' => 'Rp. ' . number_format($user->balance, 2, ',', '.'),
                'account_count' => 1, // Asumsi 1 rekening per user untuk saat ini
                'username' => $user->user_name,
                'balance_per_account' => 'Rp. ' . number_format($user->balance, 2, ',', '.'),
                'is_primary' => $user->is_primary,
            ]
        ]);
    }

    public function verifyWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya penarikan dengan status "pending" yang bisa diverifikasi.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Hanya ubah status menjadi verified
            $withdrawal->update([
                'status' => 'verified',
                'admin_verified_by' => $request->admin_id
            ]);

            DB::commit();
            Notification::create([
                'id_user' => $withdrawal->id_user,
                'id_admin' => 1, // Tidak ada admin yang memverifikasi
                'message_content' => 'Penarikan Rp. ' . $withdrawal->withdrawal_amount . ' diverifikasi oleh admin',
                'date' => now()->toDateString(),
                'status' => 'verified', // Bisa diganti jadi 'verified' jika langsung aktif
            ]);

            $result = FcmHelper::sendNotificationToDeviceUser(
                $withdrawal->user->fcm_token,
                'Penerikan berhasil disetujui',
                'Penarikan Rp. ' . $withdrawal->withdrawal_amount . ' diverifikasi oleh admin',
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan berhasil diverifikasi.',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Verifikasi gagal: ' . $e->getMessage()
            ]);
        }
    }

    // Metode opsional untuk mengambil daftar penarikan yang perlu diverifikasi
    public function getWithdrawalsWithPriority()
    {
        $withdrawals = Withdrawal::select('*')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END") // Pending paling atas
            ->orderByDesc('created_at') // Lalu urutkan dari yang terbaru
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals->map(function ($item) {
                return [
                    'id' => $item->id_withdrawal,
                    'user_name' => $item->user_name,
                    'amount' => 'Rp. ' . number_format($item->withdrawal_amount, 2, ',', '.'),
                    'date' => $item->withdrawal_date,
                    'status' => $item->status,
                ];
            })
        ]);
    }

    public function rejectWithdrawal(Request $request, $id)
    {
        $withdrawal = Withdrawal::with('user')->findOrFail($id);

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya penarikan dengan status "pending" yang bisa ditolak.'
            ], 400);
        }

        $user = User::findOrFail($withdrawal->id_user);

        if ($withdrawal->withdrawal_amount <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Jumlah penarikan tidak valid.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Kembalikan saldo user
            $user->balance += $withdrawal->withdrawal_amount;
            if (!$user->save()) {
                throw new \Exception('Gagal menyimpan perubahan saldo user');
            }

            // Hapus entri di bank_balances
            $bankBalance = BankBalance::where('id_user', $user->id_user)
                ->where('total_balance', $withdrawal->withdrawal_amount)
                ->where('transaction_type', 'cash_out')
                ->whereDate('date', $withdrawal->withdrawal_date)
                ->first();

            if ($bankBalance) {
                $bankBalance->delete(); // Hapus dari database
            }

            // Update status penarikan
            $withdrawal->update([
                'status' => 'rejected',
                'admin_verified_by' => $request->admin_id
            ]);

            DB::commit();

            Notification::create([
                'id_user' => $withdrawal->id_user,
                'id_admin' => 1, // Tidak ada admin yang memverifikasi
                'message_content' => 'Penarikan Rp. ' . $withdrawal->withdrawal_amount . ' ditolak oleh admin',
                'date' => now()->toDateString(),
                'status' => 'rejected', // Bisa diganti jadi 'verified' jika langsung aktif
            ]);

            $result = FcmHelper::sendNotificationToDeviceUser(
                $withdrawal->user->fcm_token,
                'Penerikan ditolak',
                'Penarikan sebesar Rp. ' . $withdrawal->withdrawal_amount . ' ditolak oleh admin',
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Penarikan berhasil ditolak, saldo telah dikembalikan, dan riwayat transaksi dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak penarikan: ' . $e->getMessage()
            ]);
        }
    }
}
