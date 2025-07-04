<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... (metode lain seperti getWasteTypes, dll. tetap ada)

    public function updateProfile(Request $request, $username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_name' => 'string|max:255',
            'username' => 'string|max:255|unique:users,username,' . $user->id_user,
            'nik' => 'string|max:16',
            'jenis_kelamin' => 'in:laki-laki,perempuan',
            'email' => 'string|email|unique:users,email,' . $user->id_user,
            'tanggal_lahir' => 'date',
            'phone' => 'string|max:15',
            'address' => 'string|max:255',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk foto
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
}
