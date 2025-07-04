<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SendOtp;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailController extends Controller
{
    public function sendOtpForUser(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->email;
            $otp = rand(100000, 999999);

            // Simpan OTP ke database
            OtpVerification::updateOrCreate(
                ['email' => $email],
                [
                    'otp' => $otp,
                ]
            );

            // Kirim email
            Mail::to($email)->send(new SendOtp($otp));

            return response()->json(['message' => 'OTP telah dikirim ke email Anda']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Error validasi (misalnya email tidak ditemukan)
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Error database (misalnya kolom wajib hilang atau foreign key violation)
            return response()->json([
                'message' => 'Terjadi kesalahan pada database: ' . $e->getMessage()
            ], 500);
        } catch (TransportExceptionInterface $e) {
            // Error pengiriman email
            return response()->json([
                'message' => 'Gagal mengirim OTP: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            // Error umum lainnya
            return response()->json([
                'message' => 'Terjadi kesalahan tak terduga: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendOtpForAdmin(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:admins,email']);

        $email = $request->email;
        $otp = rand(100000, 999999);

        OtpVerification::updateOrCreate(
            ['email' => $email],
            [
                'otp' => $otp,
            ]
        );

        Mail::to($email)->send(new SendOtp($otp));

        return response()->json(['message' => 'OTP telah dikirim ke email Anda']);
    }
}
