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
        return $this->hasMany(Message::class);
    }

    public function getOtherParticipant($currentUserId)
    {
        return $this->user1_id == $currentUserId ? $this->user2 : $this->user1;
    }
}
