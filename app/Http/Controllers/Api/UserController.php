<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FcmHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\Admin;
use App\Models\BankBalance;
use App\Models\User;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // ... (metode lain seperti getWasteTypes, dll. tetap ada)

    public function updateProfile(Request $request, $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|unique:users,username,' . $user->id_user,
            'nik' => 'nullable|string|max:16',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'email' => 'nullable|string|email|unique:users,email,' . $user->id_user,
            'tanggal_lahir' => 'nullable|date',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk foto
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
        }

        // Hapus foto jika diminta
        if ($request->input('remove_photo') === 'true' && $user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->photo = null;
        }

        $user->update([
            'user_name' => $request->user_name ?? $user->user_name,
            'username' => $request->username ?? $user->username,
            'nik' => $request->nik ?? $user->nik,
            'jenis_kelamin' => $request->jenis_kelamin ?? $user->jenis_kelamin,
            'email' => $request->email ?? $user->email,
            'tanggal_lahir' => $request->tanggal_lahir ?? $user->tanggal_lahir,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address ?? $user->address,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id_user,
                'user_name' => $user->user_name,
                'username' => $user->username,
                'nik' => $user->nik,
                'jenis_kelamin' => $user->jenis_kelamin,
                'email' => $user->email,
                'tanggal_lahir' => $user->tanggal_lahir,
                'phone' => $user->phone,
                'address' => $user->address,
                'photo' => $user->photo ? Storage::url($user->photo) : null,
            ]
        ]);
    }

    public function userProfile($email)
    {
        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            return response()->json([
                'status' => true,
                'message' => 'Profile showing',
                'data' => new AuthResource($user),
            ], 200);
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

    public function updateProfileAdmin(Request $request, $email)
    {
        $user = Admin::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Admin not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'admin_name' => 'nullable|string|max:255',
            'username' => 'nullable|string|unique:admins,username,' . $user->id_user,
            'nik' => 'nullable|string|max:16',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'email' => 'nullable|string|email|unique:admins,email,' . $user->id_user,
            'tanggal_lahir' => 'nullable|date',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk foto
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $photoPath = $request->file('photo')->store('photos', 'public');
            $user->photo = $photoPath;
        }

        // Hapus foto jika diminta
        if ($request->input('remove_photo') === 'true' && $user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->photo = null;
        }

        $user->update([
            'admin_name' => $request->admin_name ?? $user->admin_name,
            'username' => $request->username ?? $user->username,
            'nik' => $request->nik ?? $user->nik,
            'jenis_kelamin' => $request->jenis_kelamin ?? $user->jenis_kelamin,
            'email' => $request->email ?? $user->email,
            'tanggal_lahir' => $request->tanggal_lahir ?? $user->tanggal_lahir,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address ?? $user->address,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id_admin,
                'admin_name' => $user->useradmin_name,
                'username' => $user->username,
                'nik' => $user->nik,
                'jenis_kelamin' => $user->jenis_kelamin,
                'email' => $user->email,
                'tanggal_lahir' => $user->tanggal_lahir,
                'phone' => $user->phone,
                'address' => $user->address,
                'photo' => $user->photo ? Storage::url($user->photo) : null,
            ]
        ]);
    }

    public function userProfileAdmin($email)
    {
        try {
            // Find user by email
            $user = Admin::where('email', $email)->first();

            return response()->json([
                'status' => true,
                'message' => 'Profile showing',
                'data' => $user,
            ], 200);
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

    /**
     * Display user profile details and their cash-in transaction history.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile($email)
    {
        try {
            // Get the authenticated user
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. User not authenticated.',
                ], 401);
            }

            // Fetch user details
            $userData = [
                'id_user' => $user->id_user,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'username' => $user->username,
                'phone' => $user->phone,
                'address' => $user->address,
                'photo' => $user->photo,
                'nik' => $user->nik,
                'jenis_kelamin' => $user->jenis_kelamin,
                'tanggal_lahir' => $user->tanggal_lahir,
                'balance' => 'Rp. ' . number_format($user->balance, 2, ',', '.'),
            ];

            // Fetch cash-in transaction history from bank_balances
            $transactionHistory = BankBalance::where('id_user', $user->id_user)
                ->where('transaction_type', 'cash_in')
                ->select('id_balance', 'total_balance', 'description', 'date', 'created_at')
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id_balance' => $transaction->id_balance,
                        'total_balance' => 'Rp. ' . number_format($transaction->total_balance, 2, ',', '.'),
                        'description' => $transaction->description,
                        'date' => $transaction->date->format('Y-m-d'),
                        'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $userData,
                    'transaction_history' => $transactionHistory,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching user profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch a list of users with optional search.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserList(Request $request)
    {
        try {
            $query = User::select('id_user', 'user_name', 'email', 'photo');

            // Apply search filter if provided
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                $query->where('user_name', 'like', "%{$searchTerm}%");
            }

            // Fetch users
            $users = $query->get()->map(function ($user) {
                return [
                    'id_user' => $user->id_user,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'photo' => $user->photo, // Assuming photos are stored in storage
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching user list.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkEmailUser($email)
    {
        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            return response()->json([
                'status' => true,
                'message' => 'Email ditemukan',
                'data' => $user,
            ], 200);
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
    public function checkEmailAdmin($email)
    {
        try {
            // Find user by email
            $admin = Admin::where('email', $email)->first();

            if ($admin) {
                return response()->json([
                    'status' => true,
                    'message' => 'Email ditemukan',
                    'data' => $admin,
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Email tidak ditemukan',
                ], 400);
            }
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
}
