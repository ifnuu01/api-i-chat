<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Friendship;
use App\Models\User;

class FriendshipsController extends Controller
{
    public function addFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        $user = Auth::id();
        $friend = $request->friend_id;

        $existingFriendship = Friendship::where('user_id', $user)
            ->where('friend_id', $friend)
            ->first();

        if ($existingFriendship) {
            return response()->json([
                'message' => 'Kamu sudah berteman'
            ], 400);
        }

        // Buat friendship
        Friendship::create([
            'user_id' => $user,
            'friend_id' => $friend
        ]);

        // Buat conversation otomatis
        $user1Id = min($user, $friend);
        $user2Id = max($user, $friend);

        Conversation::firstOrCreate(
            [
                'user1_id' => $user1Id,
                'user2_id' => $user2Id
            ],
            [
                'user1_last_read_at' => now(),
                'user2_last_read_at' => now(),
                'last_message_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Anda berhasil berteman dan conversation telah dibuat'
        ]);
    }

    public function removeFriend(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        $user = Auth::id();
        $friend = $request->friend_id;

        $existingFriendship = Friendship::where('user_id', $user)
            ->where('friend_id', $friend)
            ->first();

        if (!$existingFriendship) {
            return response()->json([
                'message' => 'User belum pernah berteman'
            ], 400);
        }

        $existingFriendship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil menghapus pertemanan'
        ]);
    }

    public function getFriends()
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

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'string|nullable'
        ]);

        $currentUserId = $request->user()->id;
        $query = $request->input('query');

        if (empty($query)) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        };

        $users = User::where('id', '!=', $currentUserId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'created_at')
            ->get();

        $usersWithStatus = $users->map(function ($user) use ($currentUserId) {
            $friendship = Friendship::where('user_id', $currentUserId)
                ->where('friend_id', $user->id)
                ->first();

            $user->friendship_status = $friendship ? 'friends' : 'not_friends';
            $user->friendship_id = $friendship ? $friendship->id : null;

            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $usersWithStatus
        ]);
    }
}
