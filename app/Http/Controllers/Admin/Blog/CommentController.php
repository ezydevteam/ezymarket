<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Enums\BlogCommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Blog\BlogComment;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of blog comments.
     */
    public function index(): View
    {
        $comments = BlogComment::query();

        if (request()->filled('article')) {
            $comments->where('blog_article_id', request()->input('article'));
        }

        $comments = $comments->with(['article', 'user'])->latest()->get();

        return view('admin.blog.comments.index', compact('comments'));
    }

    /**
     * Update the specified comment status.
     */
    public function update(Request $request, BlogComment $comment): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'status' => ['required', 'string', 'in:pending,hold,published'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorJson($validator);
        }

        if ($comment->status->value === $request->status) {
            return $this->errorJson('The comment is already in the specified status');
        }

        if ($comment->isPublished() && $request->status === BlogCommentStatus::PENDING->value) {
            return $this->errorJson('Published comments cannot be changed back to pending status');
        }

        if (!$comment->isPublished() && $request->status === BlogCommentStatus::HOLD->value) {
            return $this->errorJson('Only published comments can be changed to hold status');
        }

        $comment->update(['status' => BlogCommentStatus::from($request->status)]);

        return $this->successJson('Comment status updated successfully');
    }

    /**
     * Unhold the specified comment (set status to published).
     */
    public function unhold(BlogComment $comment): JsonResponse
    {
        if (!$comment->isHold()) {
            return $this->errorJson('Only comments with hold status can be unheld');
        }

        $comment->update(['status' => BlogCommentStatus::PUBLISHED]);

        return $this->successJson('Comment unheld successfully');
    }

    /**
     * Remove the specified blog comment.
     */
    public function destroy(BlogComment $comment): JsonResponse
    {
        $comment->delete();
        return $this->successJson('Blog comment deleted successfully');
    }

    /**
     * Bulk publish blog comments.
     */
    public function bulkPublish(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                DB::beginTransaction();
                try {
                    $count = BlogComment::whereIn('id', $ids)
                        ->update(['status' => BlogCommentStatus::PUBLISHED->value]);

                    DB::commit();

                    $message = translate(':count comment(s) published successfully', ['count' => $count]);
                    return ['message' => $message, 'count' => $count];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            },
            BlogComment::class,
            ':count comment(s) published successfully',
            'Failed to publish comments'
        );
    }

    /**
     * Bulk delete blog comments.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                DB::beginTransaction();
                try {
                    $comments = BlogComment::whereIn('id', $ids)->get();

                    foreach ($comments as $comment) {
                        $comment->delete();
                    }

                    DB::commit();

                    $message = translate(':count comment(s) deleted successfully', ['count' => $comments->count()]);
                    return ['message' => $message, 'count' => $comments->count()];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            },
            BlogComment::class,
            ':count comment(s) deleted successfully',
            'Failed to delete comments'
        );
    }
}
