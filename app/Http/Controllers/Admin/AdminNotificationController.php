<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification\AdminNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminNotificationController extends Controller
{
    /**
     * Display a listing of admin notifications.
     */
    public function index(): View
    {
        $adminNotifications = AdminNotification::orderByDesc('id')->paginate(20);
        $totalNotifications = AdminNotification::count();
        $unreadCount = AdminNotification::unread()->count();

        return view('admin.notifications.admin-notifications', compact('adminNotifications', 'totalNotifications', 'unreadCount'));
    }

    /**
     * View a specific notification and redirect to its link.
     */
    public function view(AdminNotification $adminNotification): RedirectResponse
    {
        if ($adminNotification->link) {
            $adminNotification->markAsRead();
            return redirect($adminNotification->link);
        }

        return back();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function readAll(): RedirectResponse
    {
        $updatedCount = AdminNotification::unread()
            ->update(['is_unread' => false]);

        if ($updatedCount > 0) {
            toastr()->success(translate('All notifications marked as read'));
        }

        return back();
    }

    /**
     * Delete all read notifications.
     */
    public function deleteRead(): RedirectResponse
    {
        $deletedCount = AdminNotification::read()->delete();

        if ($deletedCount > 0) {
            toastr()->success(translate('Read notifications deleted successfully'));
        }

        return back();
    }
}


















