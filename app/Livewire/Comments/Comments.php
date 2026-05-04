<?php

namespace App\Livewire\Comments;

use App\Models\Product\ProductComment;
use App\Models\Product\ProductCommentReply;
use App\Traits\LivewireToastr;
use App\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class Comments extends Component
{
    use WithPagination, LivewireToastr;

    protected $paginationTheme = 'bootstrap';

    public $user;
    public $product;
    public $comment;
    public $notifyReplies = true;
    public $sort = 'newest';

    // Authentication and middleware checks are done in methods
    public function mount($product)
    {
        $this->user = authUser();
        $this->product = $product;
    }

    #[On('commentSortChanged')]
    public function updateSort($sort)
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    // Force clean URL for Livewire requests
    public function getUrl()
    {
        return route('products.show', [
            'slug' => $this->product->slug,
            'id' => $this->product->id,
            'tab' => 'comments'
        ]);
    }

    // Override Livewire's URL generation
    protected function getUpdatesQueryString()
    {
        return [];
    }

    // Prevent query string modifications
    public function getQueryString()
    {
        return [];
    }

    public function rules()
    {
        return [
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }

    public function storeComment()
    {
        // Check authentication
        if (!authUser()) {
            return redirect()->route('login');
        }

        $validator = Validator::make([
            'comment' => $this->comment,
        ], $this->rules());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return $this->toastr('error', $error);
            }
        }

        $lastCommentsCount = ProductComment::where('product_id', $this->product->id)
            ->where('user_id', $this->user->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(2))
            ->count();

        if ($lastCommentsCount >= 3) {
            return $this->toastr('error', translate('Your are writing too many comments in shorter time, please try again later'));
        }

        $user = $this->user;
        $product = $this->product;

        $comment = new ProductComment();
        $comment->user_id = $user->id;
        $comment->seller_id = $product->seller_id;
        $comment->product_id = $product->id;
        $comment->notify_replies = $this->notifyReplies ? 1 : 0;
        $comment->save();

        $commentReply = new ProductCommentReply();
        $commentReply->product_comment_id = $comment->id;
        $commentReply->user_id = $user->id;
        $commentReply->body = $this->comment;
        $commentReply->save();

        $this->comment = '';
        $this->notifyReplies = true;

        $this->dispatch('refreshCommentsCounter');

        Notification::sendProductCommentNotification($product, $comment, $commentReply, $user);

        return $this->toastr('success', translate('Your comment has been published successfully'));
    }

    public function render()
    {
        $query = ProductComment::where('product_id', $this->product->id)
            ->with('user');

        if ($this->sort === 'oldest') {
            $query->orderBy('id', 'asc');
        } else {
            $query->orderByDesc('id');
        }

        $comments = $query->paginate(20);

        return theme_view('livewire.comments.comments', [
            'comments' => $comments,
        ]);
    }
}


















