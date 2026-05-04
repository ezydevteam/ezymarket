<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request, $username)
    {
        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $notifications = $user->notifications()->paginate(20);

        $totalCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();

        return theme_view('notifications.index', compact('notifications', 'username', 'totalCount', 'unreadCount'));
    }

    public function markAsRead(Request $request, $username, $id)
    {
        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $notification = $this->notificationService->markAsRead($user, $id);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead(Request $request, $username)
    {
        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => 'All notifications are marked as read'
        ]);
    }

    public function deleteNotification(Request $request, $username, $id)
    {
        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $notification = $user->notifications()->find($id);

        if ($notification) {
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    public function deleteAllNotifications(Request $request, $username)
    {
        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $user->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully'
        ]);
    }

    public function getUnreadCount(Request $request, $username)
    {
        // Clean output buffer to prevent BOM issues
        if (ob_get_level()) {
            ob_clean();
        }

        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $count = $this->notificationService->getUnreadCount($user);

        return response()->json(['count' => $count], 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
    }

    public function getRecentNotifications(Request $request, $username)
    {
        // Clean output buffer to prevent BOM issues
        if (ob_get_level()) {
            ob_clean();
        }

        $user = $request->user();
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        $limit = $request->get('limit', 10);
        $notifications = $user->notifications()
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count()
        ], 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ]);
    }
}




















