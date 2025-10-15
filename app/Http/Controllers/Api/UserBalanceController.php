<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\BankBalance;
use App\Models\Notification;
use App\Models\WasteTransaction;
use App\Models\WasteType;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserBalanceController extends Controller
{
    /**
     * Get user balance and transaction history
     *
     * @param string $email
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBalanceAndHistory($email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $balance = $user->balance ?? 0.00;

        $history = BankBalance::where('id_user', $user->id_user)
            ->orderBy('id_balance', 'desc')
            ->get()
            ->map(function ($transaction) {
                $type = $transaction->transaction_type === 'cash_in' ? 'Cash in' : 'Cash out';
                $description = $transaction->transaction_type === 'cash_in'
                    ? "$type already in your pocket"
                    : "$type transferred to your bank account";
                return [
                    'type' => $type,
                    'amount' => "Rp " . number_format($transaction->total_balance, 2, ',', '.'),
                    'description' => $description,
                    'batch_code' => $transaction->batch_code,
                    'date' => $transaction->date,
                ];
            });

        return response()->json([
            'saldo' => "Rp " . number_format($balance, 2, ',', '.'),
            'username' => $user->user_name,
            'history' => $history
        ]);
    }
    /**
     * Create a new cash-out transaction and update user balance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTransaction(Request $request)
    {
        // Validasi input dasar
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'amount' => [
                'required',
                'numeric',
                'min:1000',
                function ($attribute, $value, $fail) use ($request) {
                    $user = User::where('email', $request->email)->first();
                    if ($user && $value > $user->balance) {
                        $fail('Saldo tidak mencukupi untuk penarikan ini.');
                    }
                },
            ],
            'description' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $amount = $request->amount;
        $description = $request->description ?? 'Penarikan tunai menunggu verifikasi';
        $date = $request->date ?? now()->toDateString();

        DB::beginTransaction();

        try {
            // Kurangi saldo pengguna
            $user->balance -= $amount;
            $user->save();

            // Buat entri withdrawal dengan status pending
            $withdrawal = Withdrawal::create([
                'id_user' => $user->id_user,
                'user_name' => $user->user_name,
                'withdrawal_date' => $date,
                'withdrawal_amount' => $amount,
                'status' => 'pending',
            ]);

            // Simpan transaksi ke bank_balances sebagai catatan
            BankBalance::create([
                'id_admin' => 1, // Sesuaikan jika diperlukan
                'id_user' => $user->id_user,
                'total_balance' => $amount,
                'transaction_type' => 'cash_out',
                'description' => $description,
                'date' => $date,
            ]);

            DB::commit();

            Notification::create([
                'id_user' => $withdrawal->id_user,
                'id_admin' => 1, // Tidak ada admin yang memverifikasi
                'message_content' => 'Penarikan Rp. ' . $withdrawal->withdrawal_amount . ' sedang menunggu verifikasi admin',
                'date' => now()->toDateString(),
                'status' => 'pending', // Bisa diganti jadi 'verified' jika langsung aktif
            ]);

            $admin = Admin::findOrFail(1);

            $result = FcmHelper::sendNotificationToDeviceAdmin(
                $admin->fcm_token,
                'Pengajuan penarikan',
                'Penarikan Rp. ' . $withdrawal->withdrawal_amount . ' sedang menunggu verifikasi admin',
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Permintaan penarikan berhasil dibuat.',
                'withdrawal_id' => $withdrawal->id_withdrawal,
                'user_name' => $withdrawal->user_name,
                'amount' => 'Rp. ' . number_format($withdrawal->withdrawal_amount, 2, ',', '.'),
                'status_withdraw' => $withdrawal->status,
                'new_balance' => 'Rp. ' . number_format($user->balance, 2, ',', '.'),
                'result' => $result,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new waste transaction and update user balance
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createWasteTransaction(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|email|exists:users,email',
        //     'waste_type' => 'required|string|exists:waste_types,waste_type',
        //     'weight' => 'required|numeric|min:0',
        //     'description' => 'nullable|string',
        //     'photo' => 'nullable|image|max:2048', // Validasi untuk file gambar, max 2MB
        // ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $wasteType = $request->waste_type;
        $weight = $request->weight;
        $description = $request->description;
        $id_admin = $request->id_admin;
        $batch_code = $request->batch_code;

        // Get price from waste_types
        $wastePrice = WasteType::where('waste_type', $wasteType)->first()->price ?? 0;
        $amount = $wastePrice * $weight;

        DB::beginTransaction();

        try {

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoFile = $request->file('photo');
                $photoName = time() . '_' . $photoFile->getClientOriginalName();
                $photoPath = $photoFile->storeAs('transaction', $photoName, 'public'); // Simpan di storage/app/public/photos
            }

            // Update user balance
            $newBalance = ($user->balance ?? 0.00) + $amount;
            $user->balance = $newBalance;
            $user->save();

            // Create waste transaction
            $wasteTransaction = WasteTransaction::create([
                'id_user' => $user->id_user,
                'waste_type' => $wasteType,
                'weight' => $weight,
                'description' => $description,
                'batch_code' => $batch_code,
                'price' => $amount,
                'photo' => $photoPath, // Simpan path foto
            ]);

            // Create bank balance entry with cash_in type
            $bankTransaction = BankBalance::create([
                'id_admin' => $id_admin, // Default admin ID, adjust as needed
                'id_user' => $user->id_user,
                'total_balance' => $amount,
                'transaction_type' => 'cash_in',
                'batch_code' => $batch_code,
                'description' => $description ?? "Cash in from waste transaction",
                'date' => now()->toDateString(),
            ]);

            DB::commit();

            Notification::create([
                'id_user' => $user->id_user,
                'id_admin' => 1, // Tidak ada admin yang memverifikasi
                'message_content' => 'Setor sampah senilai Rp. ' . $amount . ' telah berhasil ditambahkan kedalam saldo anda',
                'date' => now()->toDateString(),
                'status' => "deposit", // Bisa diganti jadi 'verified' jika langsung aktif
            ]);

            $result = FcmHelper::sendNotificationToDeviceUser(
                $user->fcm_token,
                'Setoran berhasil ditambahkan',
                'Setor sampah senilai Rp. ' . $amount . ' telah berhasil ditambahkan kedalam saldo anda',
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Waste transaction created successfully',
                'transaction_id' => $wasteTransaction->id_transaction,
                'bank_transaction_id' => $bankTransaction->id_balance,
                'transaction_type' => $bankTransaction->transaction_type,
                'new_balance' => 'Rp. ' . number_format($newBalance, 2, ',', '.'),
                'photo_path' => $photoPath ? asset('storage/' . $photoPath) : null, // URL untuk foto
                'result' => $result,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create waste transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getWasteTransactionDetail($batch_code)
    {
        // Ambil semua transaksi berdasarkan batch_code
        $transactions = WasteTransaction::with('user')
            ->where('batch_code', $batch_code)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Setoran tidak ditemukan',
            ], 404);
        }

        // Ambil user dari transaksi pertama (karena semua batch_code sama user-nya)
        $user = $transactions->first()->user;

        // Hitung total berat dan total harga
        $totalWeight = $transactions->sum('weight');
        $totalPrice = $transactions->sum('price');

        // Bentuk detail tiap jenis sampah
        $details = $transactions->map(function ($item) {
            return [
                'waste_type' => $item->waste_type,
                'weight' => $item->weight,
                'price' => $item->price,
                'description' => $item->description,
                'photo' => $item->photo ? asset('storage/' . $item->photo) : null,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'status' => 'success',
            'deposit' => [
                'batch_code' => $batch_code,
                'user_name' => $user->user_name ?? null,
                'email' => $user->email ?? null,
                'total_weight' => $totalWeight,
                'total_price' => $totalPrice,
                'total_waste_types' => $transactions->count(),
                'created_at' => $transactions->first()->created_at->format('Y-m-d H:i:s'),
            ],
            'details' => $details,
        ], 200);
    }



    public function showByDateRaw($id)
    {
        $user = User::findOrFail($id);

        // ambil semua transaksi user
        $transactions = WasteTransaction::where('id_user', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // kalau tidak ada transaksi
        if ($transactions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'User ini belum memiliki transaksi',
                'user' => [
                    'id_user' => $user->id_user,
                    'name' => $user->user_name,
                    'email' => $user->email,
                ],
                'transactions_by_date' => []
            ]);
        }

        // jika ada transaksi → kelompokkan berdasarkan tanggal
        $transactionsByDate = $transactions->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat setoran sampah user (tanpa total per jenis)',
            'user' => [
                'id_user' => $user->id_user,
                'name' => $user->user_name,
                'email' => $user->email,
            ],
            'transactions_by_date' => $transactionsByDate
        ]);
    }
}
