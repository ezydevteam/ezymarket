<?php

namespace App\Livewire\Comments;

use App\Enums\Product\CommentReportReason;
use App\Facades\Notification;
use App\Models\Product\ProductCommentReply;
use App\Models\Product\ProductCommentReport;
use App\Traits\LivewireToastr;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class CommentReport extends Component
{
    use LivewireToastr;

    public $user;
    public $productCommentReply = null;
    public $reason = '';
    public $description = '';

    public function mount()
    {
        $this->user = authUser();
    }

    public function getReasonOptions(): array
    {
        return CommentReportReason::labels();
    }

    protected function getUpdatesQueryString()
    {
        return [];
    }

    public function getQueryString()
    {
        return [];
    }

    #[On('reportProductComment')]
    public function showReportProductCommentModal($id)
    {
        // Reset form
        $this->reset(['reason', 'description']);

        // Fetch comment reply
        $commentReply = ProductCommentReply::where('id', $id)->firstOrFail();

        // Dispatch event to show modal with comment data
        $this->dispatch(
            'show-comment-report-modal',
            id: $commentReply->id,
            body: sanitizeHtml(truncateText($commentReply->body, 500), true),
            created_at: $commentReply->created_at->diffForHumans(),
            user: [
                'name' => $commentReply->user->full_name,
                'username' => $commentReply->user->username,
                'avatar' => $commentReply->user->avatar_url,
                'profile_link' => $commentReply->user->profile_link,
            ]
        );

        // Set for form submission
        $this->productCommentReply = $commentReply;
    }

    public function sendCommentReport()
    {
        // Check authentication
        if (!authUser()) {
            return redirect()->route('login');
        }

        $validator = Validator::make([
            'reason' => $this->reason,
            'description' => $this->description,
        ], [
            'reason' => ['required', 'string', 'in:' . implode(',', array_keys($this->getReasonOptions()))],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'reason.required' => translate('Please select a reason for reporting'),
            'reason.in' => translate('Please select a valid reason'),
            'description.max' => translate('Description cannot exceed 1000 characters'),
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return $this->toastr('error', $error);
            }
        }

        $existingReport = ProductCommentReport::where('product_comment_reply_id', $this->productCommentReply->id)
            ->where('user_id', $this->user->id)
            ->exists();

        if ($existingReport) {
            return $this->toastr('error', translate('You have already reported this comment'));
        }

        $report = ProductCommentReport::create([
            'user_id' => $this->user->id,
            'product_comment_reply_id' => $this->productCommentReply->id,
            'reason' => $this->reason,
            'description' => $this->description ?: null,
        ]);

        Notification::sendAdminNotification(
            translate('New Comment Reported'),
            asset('images/notifications/report.png'),
            route('admin.reports.comment-reports.index')
        );

        $this->reset(['reason', 'description']);

        $this->dispatch('refreshProductCommentReplies');
        $this->dispatch('close-modal', id: 'reportProductCommentModal');

        return $this->toastr('success', translate('Your report has been submitted successfully'));
    }

    public function render()
    {
        return theme_view('livewire.comments.comment-report');
    }
}
