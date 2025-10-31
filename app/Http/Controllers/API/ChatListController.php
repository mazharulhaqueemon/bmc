<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatListController extends Controller
{
    // GET /api/chat/list
    public function index()
    {
        $userId = Auth::id();

        // 1️⃣ Auto-populate chat_list from chat_logs if missing
        $existingChats = ChatList::where('user_id', $userId)
            ->pluck('chat_id')
            ->toArray();

        $chatLogs = DB::table('chat_logs')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->whereNotIn('chat_id', $existingChats)
            ->get();

        $insertData = [];
        foreach ($chatLogs as $log) {
            $otherUserId = ($log->sender_id === $userId) ? $log->receiver_id : $log->sender_id;

            $insertData[] = [
                'user_id' => $userId,
                'other_user_id' => $otherUserId,
                'chat_id' => $log->chat_id,
                'last_message' => $log->message,
                'last_message_at' => $log->created_at,
                'unread_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($insertData)) {
            // Insert new rows at once
            ChatList::insert($insertData);
        }

        // 2️⃣ Fetch chat list for current user with other_user info
        $chatList = ChatList::with('otherUser')
            ->where('user_id', $userId)
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($chat) {
                return [
                    'id' => $chat->id,
                    'chat_id' => $chat->chat_id,
                    'other_user' => [
                        'id' => $chat->otherUser->id ?? null,
                        'name' => $chat->otherUser->name ?? 'Unknown',
                        'avatar' => $chat->otherUser->avatar ?? 'https://cdn-icons-png.flaticon.com/512/847/847969.png',
                    ],
                    'last_message' => $chat->last_message,
                    'last_message_at' => $chat->last_message_at,
                    'unread_count' => $chat->unread_count,
                ];
            });

        return response()->json($chatList);
    }

    // POST /api/chat/list
    public function store(Request $request)
    {
        $userId = Auth::id();
        $otherUserId = $request->other_user_id;

        // Prevent duplicates
        $chat = ChatList::firstOrCreate(
            [
                'user_id' => $userId,
                'other_user_id' => $otherUserId,
            ],
            [
                'chat_id' => $userId < $otherUserId
                    ? "{$userId}_{$otherUserId}"
                    : "{$otherUserId}_{$userId}",
                'last_message' => '',
                'last_message_at' => now(),
                'unread_count' => 0,
            ]
        );

        return response()->json([
            'id' => $chat->id,
            'chat_id' => $chat->chat_id,
            'other_user' => [
                'id' => $chat->otherUser->id ?? null,
                'name' => $chat->otherUser->name ?? 'Unknown',
                'avatar' => $chat->otherUser->avatar ?? 'https://cdn-icons-png.flaticon.com/512/847/847969.png',
            ],
            'last_message' => $chat->last_message,
            'last_message_at' => $chat->last_message_at,
            'unread_count' => $chat->unread_count,
        ]);
    }
}
