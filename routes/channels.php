<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chatId}', function ($user, string $chatId) {
    $parts = explode('_', $chatId);
    if (count($parts) !== 2) {
        return false;
    }
    $a = (int)($parts[0] ?? 0);
    $b = (int)($parts[1] ?? 0);
    return (int)$user->id === $a || (int)$user->id === $b;
});
