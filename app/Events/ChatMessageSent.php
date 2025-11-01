<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatLog;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $chatId;
    public array $payload;

    public function __construct(string $chatId, ChatLog $chat)
    {
        $this->chatId = $chatId;
        $this->payload = [
            'id' => $chat->id,
            'chat_id' => $chat->chat_id,
            'sender_id' => $chat->sender_id,
            'receiver_id' => $chat->receiver_id,
            'message' => $chat->message,
            'status' => $chat->status,
            'created_at' => optional($chat->created_at)->toISOString(),
        ];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->chatId)];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}


