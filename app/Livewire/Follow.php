<?php

namespace App\Livewire;

use App\Models\Follower;
use App\Services\NotificationService;
use Livewire\Component;
use Livewire\Attributes\On;

class Follow extends Component
{
    public $user;
    public $iconButton = false;
    public $isFollowing = false;
    public $btnClass = 'soft';
    protected $notificationService;

    public function boot()
    {
        $this->notificationService = app(NotificationService::class);
    }

    public function mount($user, $iconButton = false, $btnClass = 'soft')
    {
        $this->user = $user;
        $this->iconButton = (bool) $iconButton;
        $this->btnClass = $btnClass;

        if (authUser()) {
            $this->isFollowing = authUser()->isFollowingUser($this->user->id);
        }
    }

    public function followAction()
    {
        if (!authUser()) {
            return redirect()->route('login');
        }

        $follower = authUser();

        // Prevent users from following themselves
        if ($follower->id === $this->user->id) {
            return;
        }

        if (!$this->isFollowing) {
            // Follow the user
            Follower::create([
                'follower_id' => $follower->id,
                'following_id' => $this->user->id
            ]);

            $this->isFollowing = true;

            // Send notification
            $this->notificationService->sendFollowerNotification($this->user, $follower);

            // Dispatch event to refresh the counter display
            $this->dispatch('followersUpdated');

        } else {
            // Unfollow the user - FIXED: Get the model instance first, then delete it
            $followerRecord = Follower::where('follower_id', $follower->id)
                   ->where('following_id', $this->user->id)
                   ->first();

            if ($followerRecord) {
                $followerRecord->delete(); // This will trigger the deleted event
            }

            $this->isFollowing = false;

            // Dispatch event to refresh the counter display
            $this->dispatch('followersUpdated');
        }

        // Refresh the user model to get updated counts
        $this->user->refresh();
    }

    public function render()
    {
        return theme_view('livewire.follow');
    }
}

















