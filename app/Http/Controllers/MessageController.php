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
        [X] - update : Memperbarui pesan yang sudah ada (update content, edited_at, is_edited)
        [X] - Show : Menampilkan detail pesan tertentu (1 Pesan berdasarkan id pesan)
        [X] - destroy : Menghapus pesan dari table messages (soft delete update is_deleted dan deleted_at)
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

        if ($conversation->user1_id == $currentUserId) {
            DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['user1_last_read_at' => now()]);
        } elseif ($conversation->user2_id == $currentUserId) {
            DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['user2_last_read_at' => now()]);
        }

        $page = max(1, (int) $request->query('page', 1));
        $limit = min(100, max(1, (int) $request->input('limit', 50)));
        $offset = ($page - 1) * $limit;

        $messages = DB::select("
            SELECT m.id, m.conversation_id, m.sender_id, m.reply_to_id, m.content, m.is_edited, m.edited_at, m.is_deleted, m.deleted_at, m.created_at, m.updated_at,
                u.name as sender_name, u.avatar as sender_avatar
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ", [$conversationId, $limit, $offset]);

        $messages = array_map(function ($msg) {
            $msg = (array) $msg;
            if ($msg['reply_to_id']) {
                $replyTo = DB::selectOne("
            SELECT m.id, m.content, u.name as sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.id = ?
        ", [$msg['reply_to_id']]);
                $msg['reply_to'] = $replyTo ? (array) $replyTo : null;
            } else {
                $msg['reply_to'] = null;
            }
            return $msg;
        }, $messages);

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

        DB::table('conversations')
            ->where('id', $conversationId)
            ->update(['last_message_at' => now()]);

        if ($conversation->user1_id == $currentUserId) {
            DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['user1_last_read_at' => now()]);
        } elseif ($conversation->user2_id == $currentUserId) {
            DB::table('conversations')
                ->where('id', $conversationId)
                ->update(['user2_last_read_at' => now()]);
        }

        Log::info("🚨 Kirim event MessageSent", ['id' => $messageId]);
        event(new MessageSent($messageArray));

        return response()->json([
            'success' => true,
            'data' => $messageArray
        ], 201);
    }

    public function update(Request $request)
    {
        $currentUserId = Auth::id();
        $idMessage = $request->input('message_id');
        $content = $request->input('content');

        if (empty($content)) {
            return response()->json([
                'success' => false,
                'message' => 'Konten pesan tidak boleh kosong'
            ], 422);
        }

        $messageId = DB::table('messages')
            ->where('id', $idMessage)
            ->where('sender_id', $currentUserId)
            ->update([
                'content' => $content,
                'updated_at' => now(),
                'is_edited' => true,
            ]);

        if (!$messageId) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak ditemukan atau Anda tidak memiliki izin untuk mengedit pesan ini'
            ], 404);
        }

        $message = DB::selectOne("
            SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.email as sender_email
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.id = ?
        ", [$idMessage]);

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

        $latestMessage = DB::table('messages')
            ->where('conversation_id', $message->conversation_id)
            ->orderBy('created_at', 'desc')
            ->first();

        DB::table('conversations')
            ->where('id', $message->conversation_id)
            ->update([
                'last_message_at' => $latestMessage->created_at,
            ]);

        Log::info("🚨 Kirim event MessageUpdated", ['id' => $messageId]);
        event(new MessageUpdated($messageArray));

        return response()->json([
            'success' => true,
            'data' => $message
        ], 201);
    }

    public function destroy(Request $request)
    {
        $currentUserId = Auth::id();
        $idMessage = $request->input('message_id');

        $message = DB::table('messages')
            ->where('id', $idMessage)
            ->where('sender_id', $currentUserId)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak ditemukan atau Anda tidak memiliki izin untuk menghapus pesan ini'
            ], 404);
        }

        DB::table('messages')
            ->where('id', $idMessage)
            ->update([
                'is_deleted' => true,
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        $latestMessage = DB::table('messages')
            ->where('conversation_id', $message->conversation_id)
            ->orderBy('created_at', 'desc')
            ->first();

        DB::table('conversations')
            ->where('id', $message->conversation_id)
            ->update([
                'last_message_at' => $latestMessage ? $latestMessage->created_at : null,
            ]);

        Log::info("🚨 Kirim event MessageDeleted", ['id' => $idMessage]);
        event(new MessageDeleted($idMessage, $message->conversation_id));

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus'
        ]);
    }
}
