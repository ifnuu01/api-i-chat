<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function index(string $conversationId, Request $request)
    {
        $currentUserId = Auth::id();

        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }


        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);

        $messages = Message::where('conversation_id', $conversationId)
            ->where('is_deleted', false)
            ->with(['sender:id,name,email', 'replyTo:id,content,sender_id'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $messages
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


        $conversation = Conversation::where('id', $request->conversation_id)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }

        $messageData = [
            'conversation_id' => $request->conversation_id,
            'sender_id' => $currentUserId,
            'reply_to_id' => $request->reply_to_id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
            'is_edited' => false,
            'is_deleted' => false
        ];


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('chat-files', $fileName, 'public');

            $messageData['file_url'] = Storage::url($filePath);
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['file_size'] = $file->getSize();
        }

        $message = Message::create($messageData);


        $conversation->update(['last_message_at' => now()]);


        $message->load(['sender:id,name,email', 'replyTo:id,content,sender_id']);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
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

        $message = Message::where('id', $id)
            ->where('sender_id', $currentUserId)
            ->where('is_deleted', false)
            ->first();

        if (!$message) {
            return response()->json([
                'error' => 'Message not found or access denied'
            ], 404);
        }


        if ($message->type !== 'text') {
            return response()->json([
                'error' => 'Only text messages can be edited'
            ], 400);
        }

        $message->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now()
        ]);

        $message->load(['sender:id,name,email', 'replyTo:id,content,sender_id']);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => $message
        ]);
    }

    /**
     * Delete a message
     */
    public function destroy(string $id)
    {

        $currentUserId = Auth::id();

        $message = Message::where('id', $id)
            ->where('sender_id', $currentUserId)
            ->where('is_deleted', false)
            ->first();

        if (!$message) {
            return response()->json([
                'error' => 'Message not found or access denied'
            ], 404);
        }


        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'content' => null
        ]);

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

        $message = Message::where('id', $id)
            ->where('is_deleted', false)
            ->whereHas('conversation', function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->with(['sender:id,name,email', 'replyTo:id,content,sender_id'])
            ->first();

        if (!$message) {
            return response()->json([
                'error' => 'Message not found or access denied'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $message
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


        $conversation = Conversation::where('id', $conversationId)
            ->where(function ($query) use ($currentUserId) {
                $query->where('user1_id', $currentUserId)
                    ->orWhere('user2_id', $currentUserId);
            })
            ->first();

        if (!$conversation) {
            return response()->json([
                'error' => 'Conversation not found or access denied'
            ], 404);
        }


        if ($conversation->user1_id == $currentUserId) {
            $conversation->update(['user1_last_read_at' => now()]);
        } else {
            $conversation->update(['user2_last_read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read'
        ]);
    }
}
