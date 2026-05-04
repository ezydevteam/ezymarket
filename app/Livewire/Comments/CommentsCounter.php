<?php

namespace App\Livewire\Comments;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Product\Product;

class CommentsCounter extends Component
{
    public $product;
    public $isActive;
    public $totalCommentsCount = 0;
    public $sort = 'newest';

    public function updatedSort($value)
    {
        $this->dispatch('commentSortChanged', sort: $value);
    }

    #[On('refreshCommentsCounter')]
    public function refresh()
    {
        $this->totalCommentsCount = $this->product->comments()->count();
    }

    public function mount(Product $product, $isActive)
    {
        $this->product = $product;
        $this->isActive = $isActive;
        $this->totalCommentsCount = $this->product->comments()->count();
    }

    public function getUrl()
    {
        return route('products.show', [
            'slug' => $this->product->slug,
            'id' => $this->product->id,
            'tab' => 'comments'
        ]);
    }

    protected function getUpdatesQueryString()
    {
        return [];
    }

    public function getQueryString()
    {
        return [];
    }

    public function render()
    {
        return theme_view('livewire.comments.comments-counter', [
            'count' => $this->totalCommentsCount,
        ]);
    }
}


















