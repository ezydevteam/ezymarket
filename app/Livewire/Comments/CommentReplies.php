<?php

namespace App\Livewire\Comments;

use App\Models\Product\ProductCommentReply;
use App\Traits\LivewireToastr;
use App\Traits\WithPagination;
use App\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class CommentReplies extends Component
{
    use WithPagination, LivewireToastr;

    public $user;
    public $comment;
    public $reply;
    public $perPage = 4;
    public $allRepliesLoaded = false;

    #[On('refreshProductCommentReplies')]
    public function refresh()
    {
        // Livewire v3 will automatically refresh the component
    }

    public function mount($comment)
    {
        $this->user = authUser();
        $this->comment = $comment;
    }

    public function getUrl()
    {
        return route('products.show', [
            'slug' => $this->comment->product->slug,
            'id' => $this->comment->product->id,
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

    public function storeReply()
    {
        // Check authentication
        if (!authUser()) {
            return redirect()->route('login');
        }

        $validator = Validator::make(['reply' => $this->reply], [
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return $this->toastr('error', $error);
            }
        }

        $user = $this->user;
        $comment = $this->comment;
        $product = $comment->product;

        if ($user->id != $product->seller->id &&
            $user->id != $comment->user->id) {
            return $this->toastr('error', translate('Invalid request action'));
        }

        $lastRepliesCount = ProductCommentReply::where('product_comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->where('created_at', '>', Carbon::now()->subMinutes(2))
            ->count();

        if ($lastRepliesCount >= 5) {
            return $this->toastr('error', translate('Your are writing too many replies in shorter time, please try again later'));
        }

        $commentReply = new ProductCommentReply();
        $commentReply->product_comment_id = $comment->id;
        $commentReply->user_id = $user->id;
        $commentReply->body = $this->reply;
        $commentReply->save();

        $this->reply = '';

        $this->perPage = $this->comment->replies()->count();
        $this->dispatch('refreshProductCommentReplies');

        if ($comment->notify_replies) {
            Notification::sendProductCommentReplyNotification($product, $comment, $commentReply, $user);
        }

        return $this->toastr('success', translate('Your reply has been published successfully'));
    }

    public function loadAllReplies()
    {
        $this->perPage = $this->comment->replies()->count();
    }

    public function render()
    {
        $product = $this->comment->product;

        $commentReplies = ProductCommentReply::where('product_comment_id', $this->comment->id)
            ->paginate($this->perPage);

        $totalCommentReplies = $commentReplies->total() - $this->perPage;

        if ($commentReplies->lastPage() <= $commentReplies->currentPage()) {
            $this->allRepliesLoaded = true;
        }

        return theme_view('livewire.comments.comment-replies', [
            'product' => $product,
            'totalCommentReplies' => $totalCommentReplies,
            'commentReplies' => $commentReplies,
        ]);
    }
}


















