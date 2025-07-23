<?php

namespace App\Http\Controllers;

use Google_Client;
use Illuminate\Http\Request;
use App\Models\User;

class GoogleController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string'
        ]);

        $client = new Google_Client(['client_id' =>  env('GOOGLE_CLIENT_ID_WEB')]);
        $payload = $client->verifyIdToken($request->id_token);

        if (!$payload) {
            return response()->json([
                'error' => 'Invalid Google ID Token'
            ], 401);
        }

        $email = $payload['email'];
        $name = $payload['name'];

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'email_verified_at' => now()]
        );

        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu terkena block oleh komunitas I CHAT'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function testClientId()
    {
        $clientId = env('GOOGLE_CLIENT_ID_WEB');

        try {
            $client = new Google_Client(['client_id' => $clientId]);

            return response()->json([
                'status' => 'success',
                'client_id' => $clientId,
                'message' => 'Google Client configured successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
