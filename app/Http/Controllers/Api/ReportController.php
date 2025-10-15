<?php

namespace App\Http\Controllers\Api;

use App\Exports\WasteRekapMultiYearExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function downloadReport()
    {
        $today = Carbon::today()->toDateString(); // contoh: '2025-07-16'

        // Ambil user yang punya transaksi atau penarikan pada hari ini
        $users = User::with([
            'wasteTransactions' => function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            },
            'withdrawals' => function ($query) use ($today) {
                $query->whereDate('withdrawal_date', $today);
            }
        ])->get();

        $data = [];

        foreach ($users as $user) {
            foreach ($user->wasteTransactions as $i => $transaction) {
                $data[] = [
                    'username'      => $i === 0 ? $user->username : '',
                    'phone'         => $i === 0 ? $user->phone : '',
                    'tgl_setor'     => $transaction->created_at->toDateString(),
                    'jenis_sampah'  => $transaction->waste_type,
                    'berat'         => $transaction->weight,
                    'total'         => $transaction->price,
                    'tgl_tarik'     => '',
                    'jumlah_tarik'  => '',
                    'status'  => ''
                ];
            }

            foreach ($user->withdrawals as $withdrawal) {
                $data[] = [
                    'username'      => '',
                    'phone'         => '',
                    'tgl_setor'     => '',
                    'jenis_sampah'  => '',
                    'berat'         => '',
                    'total'         => '',
                    'tgl_tarik'     => $withdrawal->withdrawal_date,
                    'jumlah_tarik'  => $withdrawal->withdrawal_amount,
                    'status' => match ($withdrawal->status) {
                                    'rejected' => 'Ditolak',
                                    'pending'  => 'Pending',
                                    default    => 'Diterima',
                                },

                ];
            }
        }

        if (empty($data)) {
            return response()->json(['message' => 'Tidak ada data untuk hari ini.'], 404);
        }

        $pdf = Pdf::loadView('reports.nasabah', ['records' => $data]);
        return $pdf->download("laporan_nasabah_$today.pdf");
    }
}
