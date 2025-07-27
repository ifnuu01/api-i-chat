<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FriendshipsController extends Controller
{
    /*
    List Controller disini:

    [X]- index : Menampilkan daftar pertemanan pengguna saat ini.
    [X]- store : Mengirim permintaan pertemanan ke pengguna lain.
    [X]- getFriendRequests : Mendapatkan daftar permintaan pertemanan yang diterima oleh pengguna saat ini.
    [X]- accept : Menerima permintaan pertemanan dari pengguna lain.
        - Jika permintaan pertemanan diterima, maka akan dibuatkan percakapan baru antara pengguna yang mengirim permintaan dan pengguna yang menerima.
    [X]- reject : Menolak permintaan pertemanan dari pengguna lain.
        - Jika menolak berarti menghapus permintaan pertemanan tersebut dari table friendships.
    [X]- destroy : Menghapus pertemanan dengan pengguna lain.
        - Jika pertemanan dihapus, maka percakapan antara kedua pengguna juga akan dihapus.
    [X]- search : Mencari pengguna berdasarkan nama atau email.
    []-cancelAddFriend : Membatalkan permintaan pertemanan yang telah dikirim.
        - Jika membatalkan berarti menghapus permintaan pertemanan tersebut dari table friendships.
    */

    public function index()
    {
        $userId = Auth::id();

        $friendships = DB::select("
            SELECT u.id, u.name, u.email, f.status, f.created_at, f.id AS friendship_id
            FROM friendships f
            JOIN users u ON (
                CASE
                    WHEN f.user_id = ? THEN u.id = f.friend_id
                    WHEN f.friend_id = ? THEN u.id = f.user_id
                END
            )
            WHERE f.user_id = ? OR f.friend_id = ?
            AND f.status = 'accepted'
            ORDER BY f.created_at DESC
        ", [$userId, $userId, $userId, $userId]);

        return response()->json($friendships);
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'friend_id' => 'required|exists:users,id|different:' . $userId
        ]);
        $friendId = $request->friend_id;

        // Memeriksa apakah sudah berteman
        $exists = DB::select("
            SELECT id FROM friendships
            WHERE (user_id = ? AND friend_id = ?)
            OR (user_id = ? AND friend_id = ?)
        ", [$userId, $friendId, $friendId, $userId]);

        if (!empty($exists)) {
            return response()->json([
                'message' => 'Kamu sudah berteman dengan pengguna ini.'
            ], 400);
        }

        $friendshipId = DB::table('friendships')->insertGetId([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Permintaan pertemanan telah dikirim',
            'friendship_id' => $friendshipId
        ]);
    }

    public function getFriendRequests()
    {
        $userId = Auth::id();

        $friendRequests = DB::select("
            SELECT u.id, u.name, u.email, f.status, f.created_at, f.id AS friendship_id
            FROM friendships f
            JOIN users u ON f.user_id = u.id
            WHERE f.friend_id = ? AND f.status = 'pending'
            ORDER BY f.created_at DESC
        ", [$userId]);

        return response()->json($friendRequests);
    }

    public function accept(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'friendship_id' => 'required|exists:friendships,id',
        ]);

        $friendshipId = $request->friendship_id;

        $friendship = DB::select("
            SELECT * FROM friendships
            WHERE id = ? AND friend_id = ? AND status = 'pending'
        ", [$friendshipId, $userId]);

        if (empty($friendship)) {
            return response()->json([
                'message' => 'Permintaan pertemanan tidak ditemukan atau sudah diterima.'
            ], 404);
        }

        DB::update("
            UPDATE friendships
            SET status = 'accepted', updated_at = NOW()
            WHERE id = ?
        ", [$friendshipId]);

        // Membuat percakapan baru antara pengguna yang menerima permintaan dan pengguna yang mengirim permintaan
        Conversation::create([
            'user1_id' => $friendship[0]->user_id,
            'user2_id' => $userId,
        ]);
        return response()->json([
            'message' => 'Permintaan pertemanan diterima',
        ]);
    }

    public function reject(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'friendship_id' => 'required|exists:friendships,id',
        ]);

        $friendshipId = $request->friendship_id;

        $friendship = DB::select("
            SELECT * FROM friendships
            WHERE id = ? AND friend_id = ? AND status = 'pending'
        ", [$friendshipId, $userId]);

        if (empty($friendship)) {
            return response()->json([
                'message' => 'Permintaan pertemanan tidak ditemukan atau sudah diterima.'
            ], 404);
        }

        DB::delete('DELETE FROM friendships WHERE id = ?', [$friendshipId]);

        return response()->json([
            'message' => 'Permintaan pertemanan ditolak',
        ]);
    }

    public function destroy(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'friendship_id' => 'required|exists:friendships,id',
        ]);
        $friendshipId = $request->friendship_id;

        $friendship = DB::select("
            SELECT * FROM friendships
            WHERE id = ? AND (user_id = ? OR friend_id = ?)
        ", [$friendshipId, $userId, $userId]);

        if (empty($friendship)) {
            return response()->json([
                'message' => 'Pertemanan tidak ditemukan.'
            ], 404);
        }
        // Menghapus percakapan pertemanan terlebih dahulu
        $friendId = $friendship[0]->user_id == $userId ? $friendship[0]->friend_id : $friendship[0]->user_id;
        DB::delete("
            DELETE FROM conversations
            WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
        ", [$userId, $friendId, $friendId, $userId]);

        DB::delete("
            DELETE FROM friendships
            WHERE id = ?
        ", [$friendshipId]);

        return response()->json([
            'message' => 'Pertemanan telah dihapus.'
        ]);
    }

    public function cancelAddFriend(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'friendship_id' => 'required|exists:friendships,id',
        ]);
        $friendshipId = $request->friendship_id;

        $friendship = DB::select("
            SELECT * FROM friendships
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ", [$friendshipId, $userId]);

        if (empty($friendship)) {
            return response()->json([
                'message' => 'Permintaan pertemanan tidak ditemukan atau sudah diterima.'
            ], 404);
        }

        DB::delete('DELETE FROM friendships WHERE id = ?', [$friendshipId]);

        return response()->json([
            'message' => 'Permintaan pertemanan telah dibatalkan.'
        ]);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $query = $request->input('query');

        /*
        Mencari pengguna berdasarkan nama atau email
        - Jika pengguna ditemukan, akan mengembalikan daftar pengguna yang cocok dengan query.
        - Jika tidak ditemukan, akan mengembalikan pesan bahwa pengguna tidak ditemukan.
        - Menambahkan kolom baru yg dibuat yaitu 'is_friend' boolean true jika accepted, false jika pending atau memang ga ada di table friendships
        */

        $users = DB::select("
            SELECT u.id, u.name, u.email,
                CASE
                    -- Sudah berteman (accepted)
                    WHEN f1.status = 'accepted' OR f2.status = 'accepted' THEN 'friends'
                    -- User ini mengirim request ke orang lain (pending)
                    WHEN f1.status = 'pending' AND f1.user_id = ? THEN 'pending_sent'
                    -- Orang lain mengirim request ke user ini (pending)
                    WHEN f2.status = 'pending' AND f2.user_id = u.id THEN 'pending_received'
                    -- Belum ada relasi
                    ELSE 'none'
                END AS friendship_status,
                CASE
                    -- Ambil ID dari f1 jika ada
                    WHEN f1.id IS NOT NULL THEN f1.id
                    -- Ambil ID dari f2 jika ada
                    WHEN f2.id IS NOT NULL THEN f2.id
                    -- Jika tidak ada relasi
                    ELSE NULL
                END AS friendship_id
            FROM users u
            LEFT JOIN friendships f1 ON (f1.user_id = ? AND f1.friend_id = u.id)
            LEFT JOIN friendships f2 ON (f2.user_id = u.id AND f2.friend_id = ?)
            WHERE u.id != ? 
            AND (u.name LIKE ? OR u.email LIKE ?)
            ORDER BY u.name
        ", [$userId, $userId, $userId, $userId, "%$query%", "%$query%"]);

        if (empty($users)) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }


        return response()->json($users);
    }
}
