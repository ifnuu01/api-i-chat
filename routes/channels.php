<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::channel('chat.{conversation_id}', function ($user, $conversation_id) {
    return DB::table('conversations')
        ->where('id', $conversation_id)
        ->where(function ($query) use ($user) {
            $query->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })->exists();
});
