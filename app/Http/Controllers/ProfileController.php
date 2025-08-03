<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /*
     [X] - StoreAvatar : Simpan/ubah image atau avatar pengguna gunakan storage dan simpan path di database
     [X] - Update Profile : Ubah data pengguna seperti nama dan email.
     [X] - Update Password : Ubah password pengguna.
    */


    public function storeAvatar(Request $request)
    {
        Log::info('storeAvatar called', $request->all());
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {

            if ($user->avatar) {
                $avatarPath = str_replace($request->getSchemeAndHttpHost() . '/storage/', '', $user->avatar);
                $existingAvatarPath = storage_path('app/public/' . $avatarPath);
                if (file_exists($existingAvatarPath)) {
                    unlink($existingAvatarPath);
                }
            }

            $avatar = $request->file('avatar');
            $uniqueName = time() . Str::uuid() . '.' . $avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('avatars', $uniqueName, 'public');
            $baseUrl = $request->getSchemeAndHttpHost();
            $avatarUrl = $baseUrl . Storage::url($path);
            $user->avatar = $avatarUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diupdate.',
                'data' => [
                    'avatar' => $avatarUrl
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Avatar tidak ditemukan.'
            ], 400);
        }
    }


    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate.',
            'data' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini tidak cocok.'
            ], 400);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ]);
    }
}
