<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{

    public function index()
    {
        $userId = Auth::id();

        $conversations = DB::select("
        SELECT 
            c.id,
            c.user1_id,
            c.user2_id,
            c.user1_last_read_at,
            c.user2_last_read_at,
            c.last_message_at,
            c.created_at,
            c.updated_at,
            -- Other participant info
            CASE 
                WHEN c.user1_id = ? THEN u2.id
                ELSE u1.id
            END AS other_participant_id,
            CASE 
                WHEN c.user1_id = ? THEN u2.name
                ELSE u1.name
            END AS other_participant_name,
            CASE 
                WHEN c.user1_id = ? THEN u2.email
                ELSE u1.email
            END AS other_participant_email,
            CASE 
                WHEN c.user1_id = ? THEN u2.avatar
                ELSE u1.avatar
            END AS other_participant_avatar,
            -- Last message info
            lm.id AS last_message_id,
            lm.content AS last_message_content,
            lm.created_at AS last_message_created_at,
            lm.is_deleted AS last_message_is_deleted,
            -- Unread count (simplified)
            COALESCE(unread.count, 0) AS unread_count
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.id
        JOIN users u2 ON c.user2_id = u2.id
        LEFT JOIN (
            SELECT 
                m1.conversation_id,
                m1.id,
                m1.content,
                m1.created_at,
                m1.is_deleted
            FROM messages m1
            INNER JOIN (
                SELECT conversation_id, MAX(created_at) as max_created_at
                FROM messages
                GROUP BY conversation_id
            ) m2 ON m1.conversation_id = m2.conversation_id 
                AND m1.created_at = m2.max_created_at
        ) lm ON c.id = lm.conversation_id
        LEFT JOIN (
            SELECT 
                m.conversation_id,
                COUNT(*) as count
            FROM messages m
            JOIN conversations conv ON m.conversation_id = conv.id
            WHERE (conv.user1_id = ? OR conv.user2_id = ?)
            -- jangan hitung pesan diri sendiri
            AND m.sender_id != ?
            AND m.created_at > CASE
                WHEN conv.user1_id = ? THEN COALESCE(conv.user1_last_read_at, '1970-01-01')
                ELSE COALESCE(conv.user2_last_read_at, '1970-01-01')
            END
            GROUP BY m.conversation_id
        ) unread ON c.id = unread.conversation_id
        WHERE c.user1_id = ? OR c.user2_id = ?
        ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
    ", [
            $userId, // CASE
            $userId, // CASE
            $userId, // CASE
            $userId, // CASE
            $userId, // unread: user1_id = ?
            $userId, // unread: user2_id = ?
            $userId, // unread: m.user_id != ?
            $userId, // unread: CASE user1_id
            $userId, // WHERE c.user1_id = ?
            $userId  // WHERE c.user2_id = ?
        ]);

        $formattedConversations = [];
        foreach ($conversations as $conversation) {
            $formattedConversations[] = [
                'id' => $conversation->id,
                'user1_id' => $conversation->user1_id,
                'user2_id' => $conversation->user2_id,
                'user1_last_read_at' => $conversation->user1_last_read_at,
                'user2_last_read_at' => $conversation->user2_last_read_at,
                'last_message_at' => $conversation->last_message_at,
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
                'other_participant' => [
                    'id' => $conversation->other_participant_id,
                    'name' => $conversation->other_participant_name,
                    'email' => $conversation->other_participant_email,
                    'avatar' => $conversation->other_participant_avatar
                ],
                'last_message' => $conversation->last_message_id ? [
                    'id' => $conversation->last_message_id,
                    'content' => $conversation->last_message_content,
                    'created_at' => $conversation->last_message_created_at,
                    'is_deleted' => $conversation->last_message_is_deleted ?? 0
                ] : null,
                'unread_count' => (int)$conversation->unread_count
            ];
        }

        return response()->json($formattedConversations);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $query = $request->input('query');

        $conversations = DB::select("
        SELECT 
            c.id,
            c.user1_id,
            c.user2_id,
            c.user1_last_read_at,
            c.user2_last_read_at,
            c.last_message_at,
            c.created_at,
            c.updated_at,
            CASE 
                WHEN c.user1_id = ? THEN u2.id
                ELSE u1.id
            END AS other_participant_id,
            CASE 
                WHEN c.user1_id = ? THEN u2.name
                ELSE u1.name
            END AS other_participant_name,
            CASE 
                WHEN c.user1_id = ? THEN u2.email
                ELSE u1.email
            END AS other_participant_email,
            lm.id AS last_message_id,
            lm.content AS last_message_content,
            lm.created_at AS last_message_created_at,
            lm.is_deleted AS last_message_is_deleted,
            COALESCE(unread.count, 0) AS unread_count
        FROM conversations c
        JOIN users u1 ON c.user1_id = u1.id
        JOIN users u2 ON c.user2_id = u2.id
        LEFT JOIN (
            SELECT 
                m1.conversation_id,
                m1.id,
                m1.content,
                m1.created_at,
                m1.is_deleted
            FROM messages m1
            INNER JOIN (
                SELECT conversation_id, MAX(created_at) as max_created_at
                FROM messages
                GROUP BY conversation_id
            ) m2 ON m1.conversation_id = m2.conversation_id 
                AND m1.created_at = m2.max_created_at
        ) lm ON c.id = lm.conversation_id
        LEFT JOIN (
            SELECT 
                m.conversation_id,
                COUNT(*) as count
            FROM messages m
            JOIN conversations conv ON m.conversation_id = conv.id
            WHERE (conv.user1_id = ? OR conv.user2_id = ?)
            AND m.sender_id != ?
            AND m.created_at > CASE
                WHEN conv.user1_id = ? THEN COALESCE(conv.user1_last_read_at, '1970-01-01')
                ELSE COALESCE(conv.user2_last_read_at, '1970-01-01')
            END
            GROUP BY m.conversation_id
        ) unread ON c.id = unread.conversation_id
        WHERE (c.user1_id = ? OR c.user2_id = ?)
        AND (
            CASE 
                WHEN c.user1_id = ? THEN (u2.name LIKE ? OR u2.email LIKE ?)
                ELSE (u1.name LIKE ? OR u1.email LIKE ?)
            END
        )
        ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
    ", [
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            $userId,
            "%$query%",
            "%$query%",
            "%$query%",
            "%$query%"
        ]);

        $formattedConversations = [];
        foreach ($conversations as $conversation) {
            $formattedConversations[] = [
                'id' => $conversation->id,
                'user1_id' => $conversation->user1_id,
                'user2_id' => $conversation->user2_id,
                'user1_last_read_at' => $conversation->user1_last_read_at,
                'user2_last_read_at' => $conversation->user2_last_read_at,
                'last_message_at' => $conversation->last_message_at,
                'created_at' => $conversation->created_at,
                'updated_at' => $conversation->updated_at,
                'other_participant' => [
                    'id' => $conversation->other_participant_id,
                    'name' => $conversation->other_participant_name,
                    'email' => $conversation->other_participant_email
                ],
                'last_message' => $conversation->last_message_id ? [
                    'id' => $conversation->last_message_id,
                    'content' => $conversation->last_message_content,
                    'created_at' => $conversation->last_message_created_at,
                    'is_deleted' => $conversation->last_message_is_deleted ?? 0
                ] : null,
                'unread_count' => (int)$conversation->unread_count
            ];
        }

        return response()->json($formattedConversations);
    }
}
