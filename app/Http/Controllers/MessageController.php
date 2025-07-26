<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    /**
     * Get messages in a conversation
     */
    public function index(string $conversationId, Request $request)
    {
        $currentUserId = Auth::id();

        $conversation = DB::select("
            SELECT id, user1_id, user2_id 
            FROM conversations 
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ", [$conversationId, $currentUserId, $currentUserId]);

        if (empty($conversation)) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);
        $offset = ($page - 1) * $limit;

        $messages = DB::select("
            SELECT 
                m.id,
                m.conversation_id,
                m.sender_id,
                m.reply_to_id,
                m.content,
                m.type,
                m.file_url,
                m.file_name,
                m.file_size,
                m.is_edited,
                m.edited_at,
                m.created_at,
                m.updated_at,
                -- Sender info
                s.name AS sender_name,
                s.email AS sender_email,
                -- Reply to message info
                rm.content AS reply_to_content,
                rs.name AS reply_to_sender_name
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            LEFT JOIN messages rm ON m.reply_to_id = rm.id
            LEFT JOIN users rs ON rm.sender_id = rs.id
            WHERE m.conversation_id = ? 
            AND m.is_deleted = false
            ORDER BY m.created_at DESC
            LIMIT ? OFFSET ?
        ", [$conversationId, $limit, $offset]);


        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'reply_to_id' => $message->reply_to_id,
                'content' => $message->content,
                'type' => $message->type,
                'file_url' => $message->file_url,
                'file_name' => $message->file_name,
                'file_size' => $message->file_size,
                'is_edited' => (bool)$message->is_edited,
                'edited_at' => $message->edited_at,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
                'sender' => [
                    'id' => $message->sender_id,
                    'name' => $message->sender_name,
                    'email' => $message->sender_email
                ],
                'reply_to' => $message->reply_to_id ? [
                    'id' => $message->reply_to_id,
                    'content' => $message->reply_to_content,
                    'sender_name' => $message->reply_to_sender_name
                ] : null
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedMessages,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'has_more' => count($messages) == $limit
            ]
        ]);
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required_if:type,text|string|max:1000',
            'type' => 'in:text,image,file',
            'reply_to_id' => 'nullable|exists:messages,id',
            'file' => 'required_if:type,image,file|file|max:10240'
        ]);

        $currentUserId = Auth::id();
        $conversationId = $request->conversation_id;


        $conversation = DB::select("
            SELECT id, user1_id, user2_id 
            FROM conversations 
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ", [$conversationId, $currentUserId, $currentUserId]);

        if (empty($conversation)) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }

        DB::beginTransaction();
        try {

            $fileUrl = null;
            $fileName = null;
            $fileSize = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $uniqueName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('chat-files', $uniqueName, 'public');

                $fileUrl = Storage::url($filePath);
                $fileName = $file->getClientOriginalName();
                $fileSize = $file->getSize();
            }


            DB::insert("
                INSERT INTO messages (
                    conversation_id, sender_id, reply_to_id, content, type,
                    file_url, file_name, file_size, is_edited, is_deleted,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, false, false, NOW(), NOW())
            ", [
                $conversationId,
                $currentUserId,
                $request->reply_to_id,
                $request->content,
                $request->type ?? 'text',
                $fileUrl,
                $fileName,
                $fileSize
            ]);


            $messageId = DB::getPdo()->lastInsertId();


            DB::update("
                UPDATE conversations 
                SET last_message_at = NOW(), updated_at = NOW()
                WHERE id = ?
            ", [$conversationId]);


            $message = DB::select("
                SELECT 
                    m.id, m.conversation_id, m.sender_id, m.reply_to_id,
                    m.content, m.type, m.file_url, m.file_name, m.file_size,
                    m.is_edited, m.edited_at, m.created_at, m.updated_at,
                    s.name AS sender_name, s.email AS sender_email,
                    rm.content AS reply_to_content, rs.name AS reply_to_sender_name
                FROM messages m
                JOIN users s ON m.sender_id = s.id
                LEFT JOIN messages rm ON m.reply_to_id = rm.id
                LEFT JOIN users rs ON rm.sender_id = rs.id
                WHERE m.id = ?
            ", [$messageId]);

            DB::commit();

            $messageData = $message[0];
            $formattedMessage = [
                'id' => $messageData->id,
                'conversation_id' => $messageData->conversation_id,
                'sender_id' => $messageData->sender_id,
                'reply_to_id' => $messageData->reply_to_id,
                'content' => $messageData->content,
                'type' => $messageData->type,
                'file_url' => $messageData->file_url,
                'file_name' => $messageData->file_name,
                'file_size' => $messageData->file_size,
                'is_edited' => (bool)$messageData->is_edited,
                'edited_at' => $messageData->edited_at,
                'created_at' => $messageData->created_at,
                'updated_at' => $messageData->updated_at,
                'sender' => [
                    'id' => $messageData->sender_id,
                    'name' => $messageData->sender_name,
                    'email' => $messageData->sender_email
                ],
                'reply_to' => $messageData->reply_to_id ? [
                    'id' => $messageData->reply_to_id,
                    'content' => $messageData->reply_to_content,
                    'sender_name' => $messageData->reply_to_sender_name
                ] : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $formattedMessage
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Failed to send message'
            ], 500);
        }
    }

    /**
     * Update/Edit a message
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $currentUserId = Auth::id();


        $message = DB::select("
            SELECT id, sender_id, type, is_deleted 
            FROM messages 
            WHERE id = ? AND sender_id = ? AND is_deleted = false AND type = 'text'
        ", [$id, $currentUserId]);

        if (empty($message)) {
            return response()->json([
                'error' => 'Message not found, access denied, or cannot be edited'
            ], 404);
        }


        $affected = DB::update("
            UPDATE messages 
            SET content = ?, is_edited = true, edited_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ", [$request->content, $id]);

        if ($affected === 0) {
            return response()->json([
                'error' => 'Failed to update message'
            ], 500);
        }


        $updatedMessage = DB::select("
            SELECT 
                m.id, m.conversation_id, m.sender_id, m.reply_to_id,
                m.content, m.type, m.file_url, m.file_name, m.file_size,
                m.is_edited, m.edited_at, m.created_at, m.updated_at,
                s.name AS sender_name, s.email AS sender_email,
                rm.content AS reply_to_content, rs.name AS reply_to_sender_name
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            LEFT JOIN messages rm ON m.reply_to_id = rm.id
            LEFT JOIN users rs ON rm.sender_id = rs.id
            WHERE m.id = ?
        ", [$id]);

        $messageData = $updatedMessage[0];
        $formattedMessage = [
            'id' => $messageData->id,
            'conversation_id' => $messageData->conversation_id,
            'sender_id' => $messageData->sender_id,
            'reply_to_id' => $messageData->reply_to_id,
            'content' => $messageData->content,
            'type' => $messageData->type,
            'file_url' => $messageData->file_url,
            'file_name' => $messageData->file_name,
            'file_size' => $messageData->file_size,
            'is_edited' => (bool)$messageData->is_edited,
            'edited_at' => $messageData->edited_at,
            'created_at' => $messageData->created_at,
            'updated_at' => $messageData->updated_at,
            'sender' => [
                'id' => $messageData->sender_id,
                'name' => $messageData->sender_name,
                'email' => $messageData->sender_email
            ],
            'reply_to' => $messageData->reply_to_id ? [
                'id' => $messageData->reply_to_id,
                'content' => $messageData->reply_to_content,
                'sender_name' => $messageData->reply_to_sender_name
            ] : null
        ];

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => $formattedMessage
        ]);
    }

    /**
     * Delete a message
     */
    public function destroy(string $id)
    {
        $currentUserId = Auth::id();


        $message = DB::select("
            SELECT id, file_url 
            FROM messages 
            WHERE id = ? AND sender_id = ? AND is_deleted = false
        ", [$id, $currentUserId]);

        if (empty($message)) {
            return response()->json([
                'error' => 'Message not found or access denied'
            ], 404);
        }


        $affected = DB::update("
            UPDATE messages 
            SET is_deleted = true, deleted_at = NOW(), content = NULL, updated_at = NOW()
            WHERE id = ?
        ", [$id]);

        if ($affected === 0) {
            return response()->json([
                'error' => 'Failed to delete message'
            ], 500);
        }


        if ($message[0]->file_url) {
            $filePath = str_replace('/storage/', '', $message[0]->file_url);
            Storage::disk('public')->delete($filePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    /**
     * Get message details
     */
    public function show(string $id)
    {
        $currentUserId = Auth::id();

        $message = DB::select("
            SELECT 
                m.id, m.conversation_id, m.sender_id, m.reply_to_id,
                m.content, m.type, m.file_url, m.file_name, m.file_size,
                m.is_edited, m.edited_at, m.created_at, m.updated_at,
                s.name AS sender_name, s.email AS sender_email,
                rm.content AS reply_to_content, rs.name AS reply_to_sender_name
            FROM messages m
            JOIN users s ON m.sender_id = s.id
            JOIN conversations c ON m.conversation_id = c.id
            LEFT JOIN messages rm ON m.reply_to_id = rm.id
            LEFT JOIN users rs ON rm.sender_id = rs.id
            WHERE m.id = ? AND m.is_deleted = false
            AND (c.user1_id = ? OR c.user2_id = ?)
        ", [$id, $currentUserId, $currentUserId]);

        if (empty($message)) {
            return response()->json([
                'error' => 'Message not found or access denied'
            ], 404);
        }

        $messageData = $message[0];
        $formattedMessage = [
            'id' => $messageData->id,
            'conversation_id' => $messageData->conversation_id,
            'sender_id' => $messageData->sender_id,
            'reply_to_id' => $messageData->reply_to_id,
            'content' => $messageData->content,
            'type' => $messageData->type,
            'file_url' => $messageData->file_url,
            'file_name' => $messageData->file_name,
            'file_size' => $messageData->file_size,
            'is_edited' => (bool)$messageData->is_edited,
            'edited_at' => $messageData->edited_at,
            'created_at' => $messageData->created_at,
            'updated_at' => $messageData->updated_at,
            'sender' => [
                'id' => $messageData->sender_id,
                'name' => $messageData->sender_name,
                'email' => $messageData->sender_email
            ],
            'reply_to' => $messageData->reply_to_id ? [
                'id' => $messageData->reply_to_id,
                'content' => $messageData->reply_to_content,
                'sender_name' => $messageData->reply_to_sender_name
            ] : null
        ];

        return response()->json([
            'success' => true,
            'data' => $formattedMessage
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        $currentUserId = Auth::id();
        $conversationId = $request->conversation_id;


        $affected = DB::update("
            UPDATE conversations 
            SET 
                user1_last_read_at = CASE WHEN user1_id = ? THEN NOW() ELSE user1_last_read_at END,
                user2_last_read_at = CASE WHEN user2_id = ? THEN NOW() ELSE user2_last_read_at END,
                updated_at = NOW()
            WHERE id = ? AND (user1_id = ? OR user2_id = ?)
        ", [$currentUserId, $currentUserId, $conversationId, $currentUserId, $currentUserId]);

        if ($affected === 0) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }
}
