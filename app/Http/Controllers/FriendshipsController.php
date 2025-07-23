<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Friendship;

class FriendshipsController extends Controller
{
    public function addFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        $user = Auth::id();
        $friend = $request->friend_id;

        $existingBlock = Friendship::where('user_id', $user)
            ->where('friend_id', $friend)
            ->first();

        if ($existingBlock) {
            return response()->json([
                'error' => 'Kamu sudah berteman'
            ], 400);
        }

        Friendship::create([
            'user_id' => $user,
            'friend_id' => $friend
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anda berhasil berteman'
        ]);
    }

    public function unFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        $user = Auth::id();
        $blocked = $request->friend_id;

        $existingBlock = Friendship::where('user$user_id', $user)
            ->where('friend_id', $blocked)
            ->first();

        if (!$existingBlock) {
            return response()->json([
                'error' => 'User belum pernah diblokir'
            ], 400);
        }

        $existingBlock->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil membuka blok'
        ]);
    }

    public function getFriendUsers()
    {
        $currentUserId = Auth::id();

        $friends = Friendship::where('user_id', $currentUserId)
            ->with('friend:id,name,email')
            ->get()
            ->pluck('friend');

        return response()->json([
            'success' => true,
            'data' => $friends
        ]);
    }
}
