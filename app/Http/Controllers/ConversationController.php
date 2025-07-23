<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ConversationController extends Controller
{
    public function index()
    {
        try {
            $currentUserId = Auth::id();

            $conversations = Conversation::where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
                ->with(['user1:id,name,email', 'user2:id,name,email', 'lastMessage'])
                ->orderBy('last_message_at', 'desc')
                ->get()
                ->map(function ($conversation) use ($currentUserId) {
                    $otherParticipant = $conversation->getOtherParticipant($currentUserId);
                    $conversation->other_participant = $otherParticipant;
                    $conversation->unread_count = $conversation->getUnreadCount($currentUserId);

                    return $conversation;
                });

            return response()->json([
                'success' => true,
                'data' => $conversations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch conversations',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or get existing conversation
     */
    public function store(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:users,id|different:' . Auth::id()
        ]);

        $currentUserId = Auth::id();
        $participantId = $request->participant_id;
        $user1Id = min($currentUserId, $participantId);
        $user2Id = max($currentUserId, $participantId);

        $conversation = Conversation::where('user1_id', $user1Id)
            ->where('user2_id', $user2Id)
            ->first();

        if ($conversation) {

            $conversation->load(['user1:id,name,email', 'user2:id,name,email']);
            $conversation->other_participant = $conversation->getOtherParticipant($currentUserId);

            return response()->json([
                'success' => true,
                'message' => 'Conversation already exists',
                'data' => $conversation
            ]);
        }

        $conversation = Conversation::create([
            'user1_id' => $user1Id,
            'user2_id' => $user2Id,
            'user1_last_read_at' => now(),
            'user2_last_read_at' => now(),
            'last_message_at' => now()
        ]);

        $conversation->load(['user1:id,name,email', 'user2:id,name,email']);
        $conversation->other_participant = $conversation->getOtherParticipant($currentUserId);

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => $conversation
        ], 201);
    }

    /**
     * Get specific conversation
     */
    public function show(string $id)
    {

        $currentUserId = Auth::id();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->with(['user1:id,name,email', 'user2:id,name,email'])
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found'
            ], 404);
        }

        $conversation->other_participant = $conversation->getOtherParticipant($currentUserId);

        return response()->json([
            'success' => true,
            'data' => $conversation
        ]);
    }

    /**
     * Mark conversation as read
     */
    public function markAsRead(string $id)
    {
        $currentUserId = Auth::id();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found'
            ], 404);
        }


        if ($conversation->user1_id == $currentUserId) {
            $conversation->update(['user1_last_read_at' => now()]);
        } else {
            $conversation->update(['user2_last_read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read'
        ]);
    }

    /**
     * Delete conversation (soft delete)
     */
    public function destroy(string $id)
    {
        $currentUserId = Auth::id();

        $conversation = Conversation::where('id', $id)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found'
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }
}
