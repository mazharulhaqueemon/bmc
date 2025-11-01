<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatLog;
use App\Models\ChatList;
use App\Events\ChatMessageSent;

class ChatController extends Controller
{
   public function sendMessage(Request $request){
        $request->validate([
            'receiver_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $sender = Auth::user();
        $receiverId = $request->receiver_id;
        $messageText = $request->message;

        // Check if sender has a plan
        $plan = $sender->plan;
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'No active plan found. Please upgrade to start chatting.',
            ], 403);
        }

        // Restrict Free plan
        if (strtolower($plan->plan_name) === 'free') {
            return response()->json([
                'success' => false,
                'message' => 'Free plan users cannot send messages. Please upgrade to start chatting.',
            ], 403);
        }

        // Check plan expiration based on activation date
        if ($plan->chat_duration_days > 0) {
            if (!$sender->plan_activated_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your plan is invalid. Please contact support.',
                ], 403);
            }
            $expirationDate = $sender->plan_activated_at->copy()->addDays($plan->chat_duration_days);
            if (now()->greaterThan($expirationDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your chat plan has expired. Please renew.',
                ], 403);
            }
        }

        // Generate unique chat_id
        $chatId = $sender->id < $receiverId
            ? $sender->id . '_' . $receiverId
            : $receiverId . '_' . $sender->id;

        $chat = ChatLog::create([
            'chat_id' => $chatId,
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'message' => $messageText,
            'status' => 'sent',
        ]);

        // Update chat lists
        ChatList::updateOrCreate(
            ['user_id' => $sender->id, 'chat_id' => $chatId, 'other_user_id' => $receiverId],
            ['last_message' => $messageText, 'last_message_at' => now(), 'unread_count' => 0]
        );

        ChatList::updateOrCreate(
            ['user_id' => $receiverId, 'chat_id' => $chatId, 'other_user_id' => $sender->id],
            [
                'last_message' => $messageText,
                'last_message_at' => now(),
            ]
        )->increment('unread_count');

        // Broadcast the message to the private chat channel
        event(new ChatMessageSent($chatId, $chat));

        return response()->json([
            'success' => true,
            'chat_id' => $chatId,
            'message' => 'Message sent successfully',
            'data' => $chat
        ]);
    }

    // Fetch chat history between authenticated user and another user
    public function getChatHistory($receiverId)
    {
        $senderId = Auth::id();

        $chatId = $senderId < $receiverId
            ? $senderId . '_' . $receiverId
            : $receiverId . '_' . $senderId;

        $messages = ChatLog::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'chat_id' => $chatId,
            'messages' => $messages
        ]);
    }
}
