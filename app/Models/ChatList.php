<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatList extends Model
{
    use HasFactory;

    protected $table = 'chat_list';

    protected $fillable = [
        'user_id',
        'chat_id',
        'other_user_id',
        'last_message',
        'last_message_at',
        'unread_count',
    ];

    // Relationships
    public function otherUser()
    {
        return $this->belongsTo(User::class, 'other_user_id');
    }
}
