<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user1_id',
        'user2_id',
        'user1_last_read_at',
        'user2_last_read_at',
        'last_message_at'
    ];

    protected $casts = [
        'user1_last_read_at' => 'datetime',
        'user2_last_read_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }

    public function getOtherParticipant($currentUserId)
    {
        if ($this->user1_id == $currentUserId) {
            return $this->user2;
        } elseif ($this->user2_id == $currentUserId) {
            return $this->user1;
        }
        return null;
    }

    public function isParticipant($userId)
    {
        return $this->user1_id == $userId || $this->user2_id == $userId;
    }

    public function getUnreadCount($userId)
    {
        $lastReadAt = $this->user1_id == $userId
            ? $this->user1_last_read_at
            : $this->user2_last_read_at;

        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastReadAt, function ($query) use ($lastReadAt) {
                return $query->where('created_at', '>', $lastReadAt);
            })
            ->count();
    }
}
