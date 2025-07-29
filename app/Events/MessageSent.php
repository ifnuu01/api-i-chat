<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

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
            'content' => $this->message['content'],
            'is_edited' => (bool) $this->message['is_edited'],
            'edited_at' => $this->message['edited_at'],
            'created_at' => $this->message['created_at'],
            'updated_at' => $this->message['updated_at'],
            'sender' => $this->message['sender'],
            'reply_to' => $this->message['reply_to'] ?? null,
        ];
    }
}
