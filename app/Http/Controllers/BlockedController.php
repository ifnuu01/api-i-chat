<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BlockedUser;
use Illuminate\Support\Facades\Auth;

class BlockedController extends Controller
{
    public function block(Request $request)
    {
        $request->validate([
            'blocked_id' => 'required|exists:users,id'
        ]);

        $blocker = Auth::id();
        $blocked = $request->blocked_id;

        $existingBlock = BlockedUser::where('blocker_id', $blocker)
            ->where('blocked_id', $blocked)
            ->first();

        if ($existingBlock) {
            return response()->json([
                'error' => 'User sudah diblokir'
            ], 400);
        }

        BlockedUser::create([
            'blocker_id' => $blocker,
            'blocked_id' => $blocked
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diblokir'
        ]);
    }

    public function unblock(Request $request)
    {
        $request->validate([
            'blocked_id' => 'required|exists:users,id'
        ]);

        $blocker = Auth::id();
        $blocked = $request->blocked_id;

        $existingBlock = BlockedUser::where('blocker_id', $blocker)
            ->where('blocked_id', $blocked)
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

    public function getBlockedUsers()
    {
        $currentUserId = Auth::id();

        $blockedUsers = BlockedUser::where('blocker_id', $currentUserId)
            ->with('blocked:id,name,email')
            ->get()
            ->pluck('blocked');

        return response()->json([
            'success' => true,
            'data' => $blockedUsers
        ]);
    }
}
