<?php

use Illuminate\Support\Facades\Broadcast;
/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User username-based notifications channel
Broadcast::channel('user-{username}', function ($user, $username) {
    return strtolower($user->username) === strtolower($username);
});

// Chatbox channel
Broadcast::channel('conversation-{conversation}', function ($user, $conversationId) {
    return $user->conversations()->where('id', $conversationId)->exists();
});

// User ID-based channel for real-time messaging
Broadcast::channel('user-{userId}', function ($user, $userId) {
    return $user->id == $userId;
});

// Presence channel for online/offline status
Broadcast::channel('presence-users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
        'avatar' => $user->avatar ?? '/default-avatar.png'
    ];
});




















