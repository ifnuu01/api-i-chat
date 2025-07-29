<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Events\MessageDeleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class MessageController extends Controller
{

    /*
        [X] - index : Mengambil kumpulan pesan dalam sebuah percakapan dari table conversation gitu
        [X] - store : Menyimpan pesan baru ke dalam table messages
        [] - update : Memperbarui pesan yang sudah ada (update content, edited_at, is_edited)
        [] - destroy : Menghapus pesan dari table messages (soft delete update is_deleted dan deleted_at)
    */

    public function index($conversationId, Request $request)
    {
        $currentUserId = Auth::id();
        $conversation = DB::selectOne("
            SELECT id, user1_id, user2_id
            FROM conversations
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ", [$conversationId, $currentUserId, $currentUserId]);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Percakapan tidak ditemukan'
            ], 404);
        }

        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->input('limit', 50)));
        $offset = ($page - 1) * $limit;

        $messages = DB::select("
            SELECT m.id, m.conversation_id, m.sender_id, m.reply_to_id, m.content, m.is_edited, m.edited_at, m.is_deleted, m.deleted_at, m.created_at, m.updated_at,
                   u.name as sender_name, u.avatar as sender_avatar
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ? AND (m.is_deleted IS NULL OR m.is_deleted = 0)
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ", [$conversationId, $limit, $offset]);

        return response()->json([
            'success' => true,
            'data' => $messages,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'has_more' => count($messages) === $limit,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $currentUserId = Auth::id();
        $conversationId = $request->input('conversation_id');
        $content = $request->input('content');
        $replyToId = $request->input('reply_to_id');

        $conversation = DB::selectOne("
            SELECT id, user1_id, user2_id
            FROM conversations
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ", [$conversationId, $currentUserId, $currentUserId]);

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Percakapan tidak ditemukan'
            ], 404);
        }

        if (empty($content)) {
            return response()->json([
                'success' => false,
                'message' => 'Konten pesan tidak boleh kosong'
            ], 422);
        }

        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $currentUserId,
            'reply_to_id' => $replyToId ?? null,
            'content' => $content,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = DB::selectOne("
            SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.email as sender_email
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.id = ?
        ", [$messageId]);

        $messageArray = (array) $message;
        $messageArray['sender'] = [
            'id' => $message->sender_id,
            'name' => $message->sender_name,
            'email' => $message->sender_email,
        ];

        if ($message->reply_to_id) {
            $replyTo = DB::selectOne("
                SELECT m.id, m.content, u.name as sender_name
                FROM messages m
                JOIN users u ON m.sender_id = u.id
                WHERE m.id = ?
            ", [$message->reply_to_id]);

            $messageArray['reply_to'] = $replyTo ? (array) $replyTo : null;
        } else {
            $messageArray['reply_to'] = null;
        }

        Log::info("🚨 Kirim event MessageSent", ['id' => $messageId]);
        event(new MessageSent($messageArray));

        return response()->json([
            'success' => true,
            'data' => $message
        ], 201);
    }
}
