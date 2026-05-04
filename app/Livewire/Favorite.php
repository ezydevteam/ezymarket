<?php

namespace App\Livewire;

use App\Models\Favorite as FavoriteModel;
use App\Facades\Notification;
use Livewire\Component;

class Favorite extends Component
{
    public $user;
    public $product;
    public $isFavorite = false;
    public $btnClass = 'outline-dark';

    public function mount($product, $btnClass = 'outline-dark')
    {
        $this->user = authUser();
        $this->product = $product;
        $this->btnClass = $btnClass;

        // Set initial favorite state
        if ($this->user) {
            $this->isFavorite = $this->user->hasProductInFavorite($this->product->id);
        }
    }

    public function addToFavorite()
    {
        // Check authentication
        if (!authUser()) {
            return redirect()->route('login');
        }

        if (!$this->isFavorite) {
            // Add to favorites
            $favorite = new FavoriteModel();
            $favorite->user_id = $this->user->id;
            $favorite->product_id = $this->product->id;
            $favorite->save();

            $this->isFavorite = true;

            Notification::sendProductFavoriteNotification($this->product, $this->user);
        } else {
            // Remove from favorites
            $favorite = $this->user->favorites->where('product_id', $this->product->id)->first();
            if ($favorite) {
                $favorite->delete();
            }

            $this->isFavorite = false;
        }

        // Refresh the user's favorites relationship
        $this->user->load('favorites');

        // Dispatch browser event for instant header update
        $this->dispatch('favorites-updated', count: $this->getFavoritesCount());
    }

    /**
     * Get fresh favorites count
     */
    protected function getFavoritesCount(): int
    {
        return FavoriteModel::where('user_id', $this->user->id)->count();
    }

    public function render()
    {
        return theme_view('livewire.favorite');
    }
}


















