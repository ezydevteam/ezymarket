<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductCommentReport;
use App\Traits\HandlesValidation;
use Illuminate\View\View;
use Illuminate\Http\{JsonResponse, Request};

class ProductCommentReportController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        $query = ProductCommentReport::query()->with([
            'user',
            'commentReply.comment.product',
            'commentReply.user'
        ]);
        $commentReports = $query->get();

        return view('admin.reports.comment-reports.index', compact('commentReports'));
    }

    public function keepComment(ProductCommentReport $productCommentReport): JsonResponse
    {
        $productCommentReport->delete();

        return $this->successJson('The comment has been kept and the report dismissed successfully');
    }

    public function deleteComment(ProductCommentReport $productCommentReport): JsonResponse
    {
        $commentReply = $productCommentReport->commentReply;
        $comment = $commentReply->comment;
        $replies = $comment->replies;

        if ($replies->first()->id === $commentReply->id) {
            $commentReply->comment->delete();
        } else {
            $commentReply->delete();
        }

        $productCommentReport->delete();
        return $this->successJson('The comment has been deleted successfully');
    }

    /**
     * Bulk keep comments (delete reports and keep comments)
     */
    public function bulkKeep(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            fn(array $ids) => ProductCommentReport::whereIn('id', $ids)->delete(),
            ProductCommentReport::class,
            ':count comments kept successfully'
        );
    }

    /**
     * Bulk delete comments (delete comments and reports)
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $reports = ProductCommentReport::with('commentReply.comment.replies')
                    ->whereIn('id', $ids)
                    ->get();

                $count = 0;
                foreach ($reports as $report) {
                    $commentReply = $report->commentReply;
                    $comment = $commentReply->comment;
                    $replies = $comment->replies;

                    if ($replies->first()->id === $commentReply->id) {
                        $comment->delete();
                    } else {
                        $commentReply->delete();
                    }

                    $report->delete();
                    $count++;
                }

                return $count;
            },
            ProductCommentReport::class,
            ':count comments deleted successfully'
        );
    }
}
