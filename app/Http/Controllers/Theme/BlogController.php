<?php

namespace App\Http\Controllers\Theme;

use App\Enums\BlogCommentStatus;
use App\Http\Controllers\Controller;
use App\Facades\Notification;
use App\Models\Blog\{BlogArticle, BlogCategory, BlogComment};
use App\Traits\HandlesValidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

/**
 * Class BlogController
 *
 * Handles all frontend blog-related functionality including listings,
 * category sorting, article viewing, and commenting.
 *
 * @package App\Http\Controllers\Theme
 */
class BlogController extends Controller
{
    use HandlesValidation;

    /**
     * Display the blog index page with optional search results.
     *
     * @return View
     */
    public function index(): View
    {
        $blogArticles = BlogArticle::query();
        $this->applySearch($blogArticles);

        $blogArticles = $blogArticles->orderbyDesc('id')->paginate(12);
        $blogArticles->appends(request()->only(['search']));

        $title = translate('Blog');
        $breadcrumbData = [
            'alias' => 'blog',
            'params' => [],
        ];

        return theme_view('blog.index', compact('blogArticles', 'title', 'breadcrumbData'));
    }

    /**
     * Display blog articles for a specific category.
     *
     * @param string $slug
     * @return View
     */
    public function category(string $slug): View
    {
        $blogCategory = BlogCategory::where('slug', $slug)->firstOrFail();
        trackView($blogCategory, 'blog_categories');
        $blogArticles = BlogArticle::where('blog_category_id', $blogCategory->id);
        $this->applySearch($blogArticles);
        $blogArticles = $blogArticles->orderbyDesc('id')->paginate(12);

        $title = $blogCategory->name;
        $breadcrumbData = [
            'alias' => 'blog_category',
            'params' => [$blogCategory],
        ];

        return theme_view('blog.index', compact('blogCategory', 'blogArticles', 'title', 'breadcrumbData'));
    }

    /**
     * Display a specific blog article.
     *
     * @param string $slug
     * @return View
     */
    public function article(string $slug): View
    {
        $blogArticle = BlogArticle::where('slug', $slug)->firstOrFail();
        trackView($blogArticle, 'blog_articles');
        $blogArticleComments = BlogComment::where('blog_article_id', $blogArticle->id)
            ->where('status', BlogCommentStatus::PUBLISHED)
            ->get();

        return theme_view('blog.article', compact('blogArticle', 'blogArticleComments'));
    }

    /**
     * Handle new comment submission for a blog article.
     *
     * @param Request $request
     * @param string $slug
     * @return RedirectResponse
     */
    public function publishComment(Request $request, string $slug): RedirectResponse
    {
        abort_if(!authUser(), 403);

        $blogArticle = BlogArticle::where('slug', $slug)->firstOrFail();

        $validator = $this->validateRequestWithInput($request, [
            'comment' => ['required', 'string', 'block_patterns'],
        ] + captchaRules());

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        // Check moderation setting
        $requireReview = settings('actions')->blog_comments_require_review ?? true;
        $status = $requireReview ? BlogCommentStatus::PENDING : BlogCommentStatus::PUBLISHED;
        $message = $requireReview
            ? translate('Your comment is under review it will be published soon')
            : translate('Your comment has been published successfully');

        $comment = BlogComment::create([
            'user_id' => authUser()->id,
            'blog_article_id' => $blogArticle->id,
            'body' => $request->comment,
            'status' => $status,
        ]);

        if ($comment) {
            if ($requireReview) {
                $this->sendAdminNotification();
            }
            return $this->successBack($message);
        }

        return $this->errorBack();
    }

    /**
     * Apply search filters to the blog articles query.
     *
     * @param Builder $query
     * @return void
     */
    private function applySearch(Builder $query): void
    {
        if (request()->has('search')) {
            $searchTerm = '%' . request('search') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->OrWhere('slug', 'like', $searchTerm)
                    ->OrWhere('body', 'like', $searchTerm)
                    ->OrWhere('short_description', 'like', $searchTerm)
                    ->orWhereHas('category', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', $searchTerm);
                    });
            });
        }
    }

    /**
     * Send notification to admin for new blog comment.
     *
     * @return void
     */
    private function sendAdminNotification(): void
    {
        $title = translate('New Blog Comment Waiting Review');
        $image = asset('images/notifications/comment.png');
        $link = route('admin.blog.comments.index');
        Notification::sendAdminNotification($title, $image, $link);
    }
}
