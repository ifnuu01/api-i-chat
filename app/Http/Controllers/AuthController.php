<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Mapping role ke abilities
        $roleAbilities = [
            'admin' => ['*'],
            'users' => ['users'],
        ];

        $abilities = $roleAbilities[$user->role] ?? null;

        if ($abilities) {
            $token = $user->createToken('auth_token', $abilities)->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'token' => $token,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ]);
        } else {
            return response()->json(['message' => 'Unauthorized role'], 403);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}
