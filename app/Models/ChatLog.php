<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    use HasFactory;

    protected $table = 'chat_logs';

    protected $fillable = [
        'chat_id',
        'sender_id',
        'receiver_id',
        'message',
        'status', 
        'created_at',
        'updated_at'
    ];
}
