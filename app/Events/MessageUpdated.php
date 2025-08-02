<?php

namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->message['conversation_id']),
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message['id'],
            'conversation_id' => $this->message['conversation_id'],
            'sender_id' => $this->message['sender_id'],
            'reply_to_id' => $this->message['reply_to_id'] ?? null,
            'reply_to' => $this->message['reply_to'] ?? null,
            'content' => $this->message['content'],
            'is_edited' => (bool) $this->message['is_edited'],
            'edited_at' => $this->message['edited_at'],
            'is_deleted' => (bool) $this->message['is_deleted'],
            'deleted_at' => $this->message['deleted_at'] ?? null,
            'created_at' => $this->message['created_at'],
            'updated_at' => $this->message['updated_at'],
            'sender' => $this->message['sender'],
            'reply_to' => $this->message['reply_to'] ?? null,
        ];
    }
}
