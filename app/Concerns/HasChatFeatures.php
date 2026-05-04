<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\User;
use App\Models\Chatbox\{Chatbox, ChatboxChat};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * HasChatFeatures Trait
 *
 * Provides chat and messaging functionality for User models.
 * Handles conversations, messages, blocking, and messaging permissions.
 *
 * @package App\Concerns
 */
trait HasChatFeatures
{
    // ===================================
    // BLOCKING CHECKS
    // ===================================

    /**
     * Check if the user is blocked by another user.
     *
     * @param int $otherId The ID of the other user
     * @return bool
     */
    public function isBlockedBy(int $otherId): bool
    {
        return self::where('id', $otherId)
            ->whereNotNull('chatbox_blocked_users->' . $this->id)
            ->exists();
    }

    /**
     * Check if the user has blocked another user.
     *
     * @param int $otherId The ID of the other user
     * @return bool
     */
    public function hasBlocked(int $otherId): bool
    {
        $blocked = $this->chatbox_blocked_users ?? [];
        return array_key_exists((string) $otherId, $blocked);
    }

    /**
     * Check if there is any block relationship between users (in either direction).
     *
     * @param int $otherId The ID of the other user
     * @return bool
     */
    public function hasBlockRelationship(int $otherId): bool
    {
        return $this->hasBlocked($otherId) || $this->isBlockedBy($otherId);
    }

    // ===================================
    // MESSAGING PERMISSIONS
    // ===================================

    /**
     * Check if the user can message another user.
     *
     * User cannot message if:
     * - They are trying to message themselves
     * - They have blocked the other user
     * - They are blocked by the other user
     *
     * @param int $otherId The ID of the other user
     * @return bool
     */
    public function canMessageUser(int $otherId): bool
    {
        if ($otherId === $this->id) {
            return false;
        }

        return !$this->hasBlockRelationship($otherId);
    }

    // ===================================
    // BLOCKING ACTIONS
    // ===================================

    /**
     * Block another user.
     *
     * @param int $otherId The ID of the user to block
     * @return bool
     */
    public function blockUser(int $otherId): bool
    {
        // Don't block yourself
        if ($otherId === $this->id) {
            return false;
        }

        $blocked = $this->chatbox_blocked_users ?? [];
        if (!array_key_exists((string) $otherId, $blocked)) {
            $blocked[(string) $otherId] = now()->toDateTimeString();
            $this->chatbox_blocked_users = $blocked;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Unblock another user.
     *
     * @param int $otherId The ID of the user to unblock
     * @return bool
     */
    public function unblockUser(int $otherId): bool
    {
        $blocked = $this->chatbox_blocked_users ?? [];
        if (array_key_exists((string) $otherId, $blocked)) {
            unset($blocked[(string) $otherId]);
            $this->chatbox_blocked_users = $blocked;
            return $this->save();
        }

        return false;
    }

    /**
     * Toggle block status for another user.
     *
     * @param int $otherId The ID of the user
     * @return bool Returns true if now blocked, false if unblocked
     */
    public function toggleBlockUser(int $otherId): bool
    {
        if ($this->hasBlocked($otherId)) {
            $this->unblockUser($otherId);
            return false;
        }

        $this->blockUser($otherId);
        return true;
    }

    // ===================================
    // CONVERSATION MANAGEMENT
    // ===================================

    /**
     * Get a conversation with another user.
     *
     * @param int $otherId The ID of the other user
     * @return Chatbox|null
     */
    public function getConversationWith(int $otherId): ?Chatbox
    {
        return Chatbox::where(function ($query) use ($otherId) {
            $query->where('user_one_id', $this->id)
                ->where('user_two_id', $otherId);
        })->orWhere(function ($query) use ($otherId) {
            $query->where('user_one_id', $otherId)
                ->where('user_two_id', $this->id);
        })->first();
    }

    /**
     * Check if user has a conversation with another user.
     *
     * @param int $otherId The ID of the other user
     * @return bool
     */
    public function hasConversationWith(int $otherId): bool
    {
        return $this->getConversationWith($otherId) !== null;
    }

    /**
     * Create or get a conversation with another user.
     *
     * @param int $otherId The ID of the other user
     * @return Chatbox|null Returns null if users cannot message each other
     */
    public function createOrGetConversation(int $otherId): ?Chatbox
    {
        // Check if messaging is allowed
        if (!$this->canMessageUser($otherId)) {
            return null;
        }

        // Check if conversation already exists
        $conversation = $this->getConversationWith($otherId);

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        return Chatbox::create([
            'user_one_id' => $this->id,
            'user_two_id' => $otherId,
        ]);
    }

    /**
     * Get all active conversations for the user.
     *
     * @param bool $paginated
     * @param int $perPage
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getActiveConversations(bool $paginated = false, int $perPage = 15)
    {
        $query = Chatbox::forUser($this->id)
            ->with(['userOne', 'userTwo', 'latestMessage.sender'])
            ->withUnreadCount($this->id)
            ->hasMessagesEfficient()
            ->orderByRecentActivity();

        return $paginated ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get unread conversations count.
     *
     * @return int
     */
    public function getUnreadConversationsCount(): int
    {
        return Chatbox::forUser($this->id)
            ->whereHas('messages', function ($query) {
                $query->unreadFor($this->id);
            })->count();
    }

    // ===================================
    // MESSAGE MANAGEMENT
    // ===================================

    /**
     * Send a message to another user.
     *
     * @param int $recipientId The ID of the recipient
     * @param string $message The message content
     * @return ChatboxChat|null Returns null if messaging is not allowed
     */
    public function sendMessage(int $recipientId, string $message): ?ChatboxChat
    {
        // Check if messaging is allowed
        if (!$this->canMessageUser($recipientId)) {
            return null;
        }

        // Create or get conversation
        $conversation = $this->createOrGetConversation($recipientId);

        if (!$conversation) {
            return null;
        }

        // Create message
        $chat = ChatboxChat::create([
            'chatbox_id' => $conversation->id,
            'sender_id' => $this->id,
            'content' => $message,
            'type' => 'text',
        ]);

        // update last_message_at
        $conversation->update(['last_message_at' => now()]);

        return $chat;
    }

    /**
     * Get messages with another user.
     *
     * @param int $otherId The ID of the other user
     * @param int $limit Maximum number of messages to retrieve
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMessagesWithUser(int $otherId, int $limit = 50)
    {
        $conversation = $this->getConversationWith($otherId);

        if (!$conversation) {
            return collect([]);
        }

        return ChatboxChat::where('chatbox_id', $conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Mark conversation as read.
     *
     * @param int $conversationId The conversation ID
     * @return bool
     */
    public function markConversationAsRead(int $conversationId): bool
    {
        $conversation = Chatbox::find($conversationId);

        if (!$conversation) {
            return false;
        }

        $conversation->messages()
            ->unreadFor($this->id)
            ->update(['read_at' => now()]);

        return true;
    }

    /**
     * Delete (soft delete) a conversation for this user.
     *
     * @param int|Chatbox $conversation
     * @return bool
     */
    public function deleteConversation($conversation): bool
    {
        if (is_numeric($conversation)) {
            $conversation = Chatbox::find($conversation);
        }

        if (!$conversation) {
            return false;
        }

        if ($conversation->user_one_id == $this->id) {
            return $conversation->update(['user_one_deleted_at' => now()]);
        } elseif ($conversation->user_two_id == $this->id) {
            return $conversation->update(['user_two_deleted_at' => now()]);
        }

        return false;
    }

    // ===================================
    // BLOCKED USERS MANAGEMENT
    // ===================================

    /**
     * Get all users blocked by this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBlockedUsers()
    {
        $blockedIds = array_keys($this->chatbox_blocked_users ?? []);
        return User::whereIn('id', $blockedIds)->get();
    }

    /**
     * Get all users who have blocked this user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersWhoBlockedMe()
    {
         return User::whereNotNull('chatbox_blocked_users->' . $this->id)->get();
    }

    /**
     * Get blocked users count.
     *
     * @return int
     */
    public function getBlockedUsersCount(): int
    {
        return count($this->chatbox_blocked_users ?? []);
    }

    /**
     * Unblock all users.
     *
     * @return int Number of users unblocked
     */
    public function unblockAllUsers(): int
    {
        $count = $this->getBlockedUsersCount();
        if ($count > 0) {
            $this->chatbox_blocked_users = [];
            $this->save();
        }
        return $count;
    }

    // ===================================
    // RELATIONSHIPS
    // ===================================

    /**
     * Get the conversations for the user.
     *
     * @return HasMany
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Chatbox::class, 'user_one_id')
            ->orWhere('user_two_id', $this->id);
    }

    /**
     * Get the messages sent by the user.
     *
     * @return HasMany
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatboxChat::class, 'sender_id');
    }

    // Obsolete relations 'blocks' and 'blockedBy' were removed in favor of JSON architecture
}
