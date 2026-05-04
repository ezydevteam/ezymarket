<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;

class FeedbackController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        $feedbacks = Feedback::with('user')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.reports.feedback.index', compact('feedbacks'));
    }

    public function updateStatus(Request $request, Feedback $feedback): JsonResponse
    {
        $validation = $this->validateRequestJson($request, [
            'status' => 'required|in:reviewed,resolved',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        if ($validation instanceof JsonResponse) {
            return $validation;
        }

        $data = $validation->validated();

        $feedback->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? $feedback->admin_notes,
            'reviewed_at' => $data['status'] === 'reviewed' ? now() : null,
            'resolved_at' => $data['status'] === 'resolved' ? now() : null
        ]);

        return $this->successJson('Feedback status updated successfully');
    }

    public function destroy(Feedback $feedback): JsonResponse
    {
        $this->deleteScreenshots($feedback);
        $feedback->delete();

        return $this->successJson('Feedback deleted successfully');
    }

    public function bulkReview(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            fn(array $ids) => Feedback::whereIn('id', $ids)->update([
                'status' => FeedbackStatus::REVIEWED,
                'reviewed_at' => now()
            ]),
            Feedback::class,
            ':count feedback(s) marked as reviewed'
        );
    }

    public function bulkResolve(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            fn(array $ids) => Feedback::whereIn('id', $ids)->update([
                'status' => FeedbackStatus::RESOLVED,
                'resolved_at' => now()
            ]),
            Feedback::class,
            ':count feedback(s) marked as resolved'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $feedbacks = Feedback::whereIn('id', $ids)->get();

                foreach ($feedbacks as $feedback) {
                    $this->deleteScreenshots($feedback);
                }

                return Feedback::whereIn('id', $ids)->delete();
            },
            Feedback::class,
            ':count feedback(s) deleted successfully'
        );
    }

    /**
     * Delete feedback screenshots from storage
     */
    private function deleteScreenshots(Feedback $feedback): void
    {
        if (!$feedback->hasScreenshots()) {
            return;
        }

        foreach ($feedback->screenshots as $screenshot) {
            removeFileFromStorage($screenshot, 'public');
        }
    }
}
