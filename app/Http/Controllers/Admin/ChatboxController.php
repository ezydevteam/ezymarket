<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Settings};
use App\Models\Chatbox\ChatboxChat;
use Illuminate\Http\Request;

class ChatboxController extends Controller
{
    const HISTORY_DAYS = 7;

    /* -------------------------------------------------------------
     |  1. System-wide keyword / rule settings
     |-------------------------------------------------------------*/

	public function index()
	{
		$chatboxSettings = settings('chatbox');

		return view('admin.chatbox.index', compact('chatboxSettings'));
	}

    public function store(Request $request)
	{
		$data = $request->validate([
			'chatbox_system_enabled'  => 'sometimes|boolean',
			'keywords'        => 'nullable|array',
		]);

		Settings::updateSettings('chatbox', [
			'status'  => $request->has('chatbox_system_enabled'),
			'banned_keywords' => $data['keywords'] ?? settings('chatbox')->banned_keywords ?? collect([]),
		]);

        toastr()->success(translate('Chatbox settings updated'));
		return back();
	}

    /* -------------------------------------------------------------
     |  2. Chat history search
     |-------------------------------------------------------------*/

    public function history(Request $request)
    {
        $query = ChatboxChat::with(['sender', 'conversation.userOne', 'conversation.userTwo'])
                                    ->where('created_at', '>=', now()->subDays(self::HISTORY_DAYS));

        if ($request->filled('user_id')) {
            $query->where('sender_id', $request->user_id);
        }

        if ($request->filled('keyword')) {
            $query->where('content', 'like', '%' . $request->keyword . '%');
        }

        $messages = $query->latest()->paginate(50);

        return view('admin.chatbox.history', compact('messages'));
    }

    /* -------------------------------------------------------------
     |  3. Per-user bans (disable messaging)
     |-------------------------------------------------------------*/

    public function banUser(User $user)
    {
        $user->update(['can_message' => false]);

        toastr()->success(translate("{$user->full_name} can no longer use chatbox"));
		return back();
    }

    public function unbanUser(User $user)
    {
        $user->update(['can_message' => true]);

        toastr()->success(translate("{$user->full_name} messaging re-enabled"));
		return back();
    }
}

















