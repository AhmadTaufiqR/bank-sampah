<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function authLogin(Request $request)
    {
        try {
            $response = User::where('email', '=', $request->email)->first();
            if ($response && Hash::check($request->password, $response->password)) {
                return response()->json([
                    'status' => 'true',
                    'message' => 'user find',
                    'token' => $response->createToken('auth_token')->accessToken,
                    'data' => new authResource($response),
                ], 200);
            }

            return response()->json([
                'status' => 'false',
                'message' => 'user not found'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'false',
                'message' => $th->getMessage(),
            ], 402);
        }
    }

    public function authRegister(Request $request)
    {
        try {
            $request->validate(
                [
                    'name' => 'required|max:255',
                    'email' => 'required|email',
                    'password' => 'required|min:8',
                ]
            );

            $newUser = new User();
            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->password = Hash::make($request->password);
            $newUser->save();

            if ($newUser) {
                return response()->json([
                    'status' => 'true',
                    'message' => 'create user successfully',
                    'data' => new AuthResource($newUser),
                ]);
            }

            return response()->json([
                'status' => 'false',
                'message' => 'user cannot create'
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'false',
                'message' => 'error' + $th->getMessage(),
            ], 402);
        }
    }
}
