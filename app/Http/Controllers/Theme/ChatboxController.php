<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;

//use App\Events\NewMessageEvent;
use App\Enums\User\UserStatus;
use App\Models\Chatbox\{
	Chatbox,
	ChatboxChat
};
use App\Models\User;
use App\Services\MessageFilterService;
use Ably\AblyRest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ChatboxController extends Controller
{
	public function __construct(private MessageFilterService $filter){}

    /* -------------------------------------------------------- *
     *  Web Routes (HTML Views)
     * -------------------------------------------------------- */

	/**
	 * Show the main chatbox interface
	 */
	public function index(Request $request)
	{
		return theme_view('chatbox');
	}

    /* -------------------------------------------------------- *
     *  API Routes (JSON Data)
     * -------------------------------------------------------- */
	/**
	 * Get paginated list of conversations (API endpoint)
	 */
	public function conversations(Request $request): JsonResponse
	{
		try {
			$user = Auth::user();

			$conversations = $user->getActiveConversations(true, 15);

			$formattedData = $this->formatConversationsData($conversations->items());

			// Clean output buffer to prevent BOM issues
			if (ob_get_level()) {
				ob_clean();
			}

			return response()->json([
				'data' => $formattedData['conversations'],
				'message' => $formattedData['messages'],
				'current_page' => $conversations->currentPage(),
				'last_page' => $conversations->lastPage(),
				'per_page' => $conversations->perPage(),
				'total' => $conversations->total(),
				'from' => $conversations->firstItem(),
				'to' => $conversations->lastItem(),
			], 200, [
				'Content-Type' => 'application/json; charset=UTF-8'
			]);
		} catch (\Exception $e) {
			\Log::error('ChatboxController::conversations error', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return response()->json(['error' => 'Failed to load chats'], 500);
		}
	}

	/**
	 * Get recent conversations for dropdown (API endpoint)
	 */
	public function recent(Request $request): JsonResponse
	{
		try {
			$user = Auth::user();

			$conversations = Chatbox::forUser($user->id)
				->with(['userOne', 'userTwo', 'latestMessage.sender'])
				->withUnreadCount($user->id)
				->orderByDesc('last_message_at')
				->limit(10)
				->get();

			$formattedData = $this->formatConversationsData($conversations);

			// Clean output buffer to prevent BOM issues
			if (ob_get_level()) {
				ob_clean();
			}

			return response()->json([
				'data' => $formattedData['conversations'],
				'message' => $formattedData['messages']
			], 200, [
				'Content-Type' => 'application/json; charset=UTF-8'
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to load recent chats'], 500);
		}
	}

	/**
	 * Get messages for a specific conversation (API endpoint)
	 */
	public function conversation(Request $request, Chatbox $conversation): JsonResponse
	{
		$this->authorizeConversation($conversation);

		try {
			$userId = Auth::id();
			$wasRecentlyReactivated = $conversation->wasRecentlyReactivated();

			$messages = $conversation->messages()
				->with('sender')
				->notDeleted()
				->orderBy('created_at', 'asc')
				->get();

			return response()->json([
				'data' => $messages,
				'was_reactivated' => $wasRecentlyReactivated,
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to load messages'], 500);
		}
	}

	/**
	 * Start new conversation from external link
	 */


	/**
	 * Unread message count
	 */
	public function getUnreadCount(): JsonResponse
	{
		$user = Auth::user();

		$unreadCount = $user->getUnreadConversationsCount();

		// Clean output buffer to prevent BOM issues
		if (ob_get_level()) {
			ob_clean();
		}

		return response()->json(['unread_count' => $unreadCount], 200, [
			'Content-Type' => 'application/json; charset=UTF-8'
		]);
	}

	/**
	 * MarkAllAsRead
	 */
	public function markUnreadAsRead(Chatbox $conversation): JsonResponse
	{
		if (Auth::user()->markConversationAsRead($conversation->id)) {
			return response()->json(['status' => 'marked_as_read']);
		}

		return response()->json(['error' => 'Unauthorized'], 403);
	}


	/**
	 * Delete a conversation
	 */
	public function destroyConversation(Chatbox $conversation): JsonResponse
	{
		$this->authorizeConversation($conversation);

		try {
			$user = Auth::user();

			if ($user->deleteConversation($conversation)) {
				$conversation->refresh();
				return response()->json(['status' => 'deleted']);
			}

			return response()->json(['error' => 'Failed to delete conversation'], 500);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to delete conversation'], 500);
		}
	}

    /* -------------------------------------------------------- *
     *  Messages
     * -------------------------------------------------------- */

	/**
	 * Send a new message
	 */
	public function store(Request $request): JsonResponse
	{
		$request->validate([
			'recipient_id' => 'required|exists:users,id',
			'content'      => 'nullable|string|max:1000',
			'type'         => 'in:text,emoji',
		]);

		try {
			$sender    = Auth::user();
			$recipient = User::findOrFail($request->recipient_id);

			$conversation = $sender->createOrGetConversation($recipient->id);

			if (!$conversation) {
				return response()->json(['error' => 'You are not allowed to message'], 403);
			}

			$this->reactivateConversation($conversation, $sender->id);

			$message = null;
			if ($request->filled('content')) {
				$filtered = $this->filter->filter($request->content);
				if ($filtered === null) {
					return response()->json(['error' => 'Blocked content'], 400);
				}

				$message = ChatboxChat::create([
					'chatbox_id' => $conversation->id,
					'sender_id'          => $sender->id,
					'content'            => $filtered,
					'type'               => $request->type ?? 'text',
					'is_filtered'        => $filtered !== $request->content,
				]);

				$conversation->update(['last_message_at' => now()]);

				$message->load('sender', 'conversation');
				$message->sender->avatar = $message->sender->avatar_url;
				$message->sender->name = $message->sender->full_name;
				$message->conversation_id = $conversation->id;

				$this->broadcastToConversation($message, $conversation);
			}

			$response = [
				'success' => true,
				'conversation_id' => $conversation->id,
				'message' => $message ? 'Message sent successfully' : 'Conversation created successfully'
			];

			if ($message) {
				$response['message_data'] = $message;
			}

			return response()->json($response);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to process request'], 500);
		}
	}

	/**
	 * Delete a message
	 */
	public function deleteMessage(ChatboxChat $message): JsonResponse
	{
		if ($message->sender_id !== Auth::id()) {
			return response()->json(['error' => 'Unauthorized'], 403);
		}

		try {
			$message->update(['is_deleted_by_sender' => true]);
			return response()->json(['status' => 'deleted']);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to delete message'], 500);
		}
	}

	/**
	 * Search users for messaging
	 */
	public function searchUsers(Request $request): JsonResponse
	{
		$request->validate([
			'query' => 'required|string|min:2|max:50'
		]);

		try {
			$query = $request->input('query');
			$currentUserId = Auth::id();
			$limit = 10;

			$following = Auth::user()->followings()->pluck('following_id')->toArray();
			$followers = Auth::user()->followers()->pluck('follower_id')->toArray();

			$users = User::where('id', '!=', $currentUserId)
				->where(function ($q) use ($query) {
					$q->where('username', 'LIKE', "%{$query}%")
						->orWhere('firstname', 'LIKE', "%{$query}%")
						->orWhere('lastname', 'LIKE', "%{$query}%")
						->orWhere('id', 'LIKE', "%{$query}%");
				})
				->where('status', UserStatus::ACTIVE)
				->limit($limit * 2)
				->get(['id', 'firstname', 'lastname', 'username', 'avatar', 'profile_description', 'last_active_at'])
				->map(function ($user) use ($following, $followers) {
					return $this->formatUserForSearch($user, $following, $followers);
				})
				->sortBy(['priority', 'name'])
				->take($limit)
				->values();

			// Clean output buffer to prevent BOM issues
			if (ob_get_level()) {
				ob_clean();
			}

			return response()->json([
				'users' => $users,
				'query' => $query,
				'total' => $users->count()
			], 200, [
				'Content-Type' => 'application/json; charset=UTF-8'
			]);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Search failed'], 500);
		}
	}

	/* -------------------------------------------------------- *
     *  Blocking
     * -------------------------------------------------------- */

	public function block(Request $request): JsonResponse
	{
		$request->validate(['user_id' => 'required|exists:users,id']);

		try {
            $user = Auth::user();
            $user->blockUser($request->user_id);

			return response()->json(['status' => 'blocked']);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to block user'], 500);
		}
	}

	public function unblock(Request $request): JsonResponse
	{
		$request->validate(['user_id' => 'required|exists:users,id']);

		try {
            $user = Auth::user();
            $user->unblockUser($request->user_id);

			return response()->json(['status' => 'unblocked']);
		} catch (\Exception $e) {
			return response()->json(['error' => 'Failed to unblock user'], 500);
		}
	}

    /* -------------------------------------------------------- *
     *  Helper Methods
     * -------------------------------------------------------- */
	/**
	 * Format conversations data with complete user information
	 */
	private function formatConversationsData($conversations): array
	{
		$conversationData = [];
		$messageData = [];
		$currentUser = Auth::user();
		$currentUserId = Auth::id();

		foreach ($conversations as $conversation) {
			$userOneData = null;
			$userTwoData = null;

			if ($conversation->userOne) {
				$user = $conversation->userOne;
				$userOneData = [
					'id' => $user->id,
					'name' => $user->full_name,
					'username' => $user->username,
					'avatar' => $user->avatar_url,
					'is_blocked' => $currentUser->hasBlocked($user->id),
					'is_blocked_by_other' => $user->hasBlocked($currentUserId),
					'is_last_active' => $user->last_active_formatted,
				];
			}

			if ($conversation->userTwo) {
				$user = $conversation->userTwo;
				$userTwoData = [
					'id' => $user->id,
					'name' => $user->full_name,
					'username' => $user->username,
					'avatar' => $user->avatar_url,
					'is_blocked' => $currentUser->hasBlocked($user->id),
					'is_blocked_by_other' => $user->hasBlocked($currentUserId),
					'is_last_active' => $user->last_active_formatted,
				];
			}

			$conversationData[] = [
				'id' => $conversation->id,
				'user_one_id' => $conversation->user_one_id,
				'user_two_id' => $conversation->user_two_id,
				'last_message_at' => $conversation->last_message_at,
				'unread_count' => $conversation->unread_count ?? 0,
				'user_one' => $userOneData,
				'user_two' => $userTwoData,
			];

			if ($conversation->latestMessage) {
				$message = $conversation->latestMessage;
				$messageData[] = [
					'id' => $message->id,
					'content' => $message->content,
					'type' => $message->type ?? 'text',
					'sender_id' => $message->sender_id,
					'conversation_id' => $conversation->id,
					'created_at' => $message->created_at->format('Y-m-d H:i:s'),
					'created_at_human' => $message->created_at->diffForHumans(),
				];
			} else {
				$messageData[] = null;
			}
		}

		return [
			'conversations' => $conversationData,
			'messages' => $messageData
		];
	}

	/**
	 * Format user data for search results
	 */
	private function formatUserForSearch(User $user, array $following, array $followers): array
	{
		if (in_array($user->id, $following)) {
			$relationship = 'following';
			$priority = 1;
		} elseif (in_array($user->id, $followers)) {
			$relationship = 'follower';
			$priority = 2;
		} else {
			$relationship = 'other';
			$priority = 3;
		}

		$isOnline = $user->last_active_at &&
			$user->last_active_at->diffInMinutes(now()) <= 10;

		return [
			'id' => $user->id,
			'name' => $user->full_name,
			'username' => $user->username,
			'avatar' => $user->avatar_url,
			'bio' => $user->basic_info['bio'] ?? '',
			'is_online' => $isOnline,
			'is_blocked' => Auth::user()->hasBlocked($user->id),
			'relationship' => $relationship,
			'priority' => $priority
		];
	}

	private function broadcastToConversation($message, $conversation)
	{
		$recipientId = $message->sender_id == $conversation->user_one_id
			? $conversation->user_two_id
			: $conversation->user_one_id;

		if (config('broadcasting.default') === 'ably') {
			$ably = new AblyRest(config('broadcasting.connections.ably.key'));

			$messageData = [
				'id' => $message->id,
				'content' => $message->content,
				'type' => $message->type ?? 'text',
				'sender_id' => $message->sender_id,
				'conversation_id' => $conversation->id,
				'created_at' => $message->created_at->format('Y-m-d H:i:s'),
				'created_at_human' => $message->created_at->diffForHumans(),
				'sender' => [
					'id' => $message->sender->id,
					'name' => $message->sender->full_name,
					'username' => $message->sender->username,
					'avatar' => $message->sender->avatar_url,
					'is_blocked' => Auth::user()->hasBlocked($message->sender->id),
					'is_blocked_by_other' => $message->sender->hasBlocked(Auth::id()),
					'is_last_active'  => $message->sender->last_active_formatted,
				]
			];

			$channel = $ably->channels->get("user-{$recipientId}");
			$channel->publish('new-message', [
				'message' => $messageData,
				'conversation_id' => $conversation->id
			]);

			$conversationChannel = $ably->channels->get("conversation:{$conversation->id}");
			$conversationChannel->publish('new-message', [
				'message' => $messageData,
				'conversation_id' => $conversation->id
			]);
		}
	}

	/**
	 * Reactivate a conversation by clearing deleted_at fields
	 */
	private function reactivateConversation(Chatbox $conversation, int $userId): void
	{
		$updates = [];

		if ($conversation->user_one_id == $userId && $conversation->user_one_deleted_at) {
			$updates['user_one_deleted_at'] = null;
		}

		if ($conversation->user_two_id == $userId && $conversation->user_two_deleted_at) {
			$updates['user_two_deleted_at'] = null;
		}

		if (!empty($updates)) {
			$conversation->update($updates);
		}
	}

	/**
	 * Check if user has access to conversation
	 */
	private function authorizeConversation(Chatbox $conversation): void
	{
		if (
			(int) $conversation->user_one_id !== (int) Auth::id()
			&& (int) $conversation->user_two_id !== (int) Auth::id()
		) {
			abort(403, 'Access denied');
		}
	}


}
