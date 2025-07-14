<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Admin;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function authLogin(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'email' => 'required|email',
                'fcm_token' => 'required',
            ]);

            // Find user by email
            $user = User::where('email', $request->email)->first();


            // Check if user exists and password matches
            if ($user && Hash::check($request->user_password, $user->user_password)) {
                $user->update(
                    ['fcm_token' => $request->fcm_token]
                );
                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'data' => new AuthResource($user),
                ], 200);
            }



            // Return specific error for incorrect credentials
            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password',
            ], 401);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }


    public function authRegister(Request $request)
    {
        try {
            // Validate input based on schema requirements
            $request->validate([
                'user_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'photo' => 'nullable|string|max:255',
                'nik' => 'nullable|string|max:255',
                'jenis_kelamin' => 'nullable|string|in:Laki-laki,Perempuan', // Adjust as needed
                'tanggal_lahir' => 'nullable|date',
            ]);

            // Create new user
            $user = new User();
            $user->user_name = $request->user_name;
            $user->user_password = Hash::make($request->user_password);
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->photo = $request->photo;
            $user->nik = $request->nik;
            $user->jenis_kelamin = $request->jenis_kelamin;
            $user->tanggal_lahir = $request->tanggal_lahir;
            // Default values for nullable fields are handled by schema (balance, withdrawal_count, withdrawal_amount, is_primary)
            $user->save();

            // Buat notifikasi untuk user yang baru terdaftar
            Notification::create([
                'id_user' => $user->id_user,
                'id_admin' => 1, // Tidak ada admin yang memverifikasi
                'message_content' => 'Selamat datang di aplikasi bank sampah!',
                'date' => now()->toDateString(),
                'status' => "register", // Bisa diganti jadi 'verified' jika langsung aktif
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => new AuthResource($user),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred: ' . $th->getMessage(),
            ], 500);
        }
    }
    public function authRegisterAdmin(Request $request)
    {
        try {

            $adminOld = Admin::where('email', '=', $request->email)->first();

            if ($adminOld) {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Admin already exists'
                ], 400);
            }

            $newUser = new Admin();
            $newUser->admin_name = $request->admin_name;
            $newUser->email = $request->email;
            // $newUser->admin_password = Hash::make($request->admin_password);
            $newUser->phone = $request->phone;
            $newUser->address = $request->address;
            $newUser->photo = $request->photo;
            $newUser->save();

            if ($newUser) {
                return response()->json([
                    'status' => 'true',
                    'message' => 'Create user successfully',
                    'data' => $newUser,
                ]);
            }

            return response()->json([
                'status' => 'false',
                'message' => 'User cannot create'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'false',
                'message' => 'error' + $th->getMessage(),
            ], 402);
        }
    }

    public function authLoginAdmin(Request $request)
    {
        try {
            $response = Admin::where('email', '=', $request->email)->first();

            $response->update(
                ['fcm_token' => $request->fcm_token]
            );

            return response()->json([
                'status' => 'true',
                'message' => 'Admin find',
                'data' => $response,
            ], 200);

            // return response()->json([
            //     'status' => 'false',
            //     'message' => 'Admin not found'
            // ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'false',
                'message' => $th->getMessage(),
            ], 402);
        }
    }
}
