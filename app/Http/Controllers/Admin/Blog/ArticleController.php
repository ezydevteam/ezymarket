<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog\{BlogArticle, BlogCategory};
use App\Traits\HandlesValidation;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of blog articles.
     */
    public function index(Request $request): View
    {
        $categories = BlogCategory::all();

        $articles = BlogArticle::query()->with('category')
            ->when($request->has('category') && $request->category != '', function ($query) use ($request) {
                $query->where('blog_category_id', $request->category);
            })
            ->withCount('comments')
            ->get();

        return view('admin.blog.articles.index', compact('categories', 'articles'));
    }

    /**
     * Generate slug for blog article.
     */
    public function slug(Request $request): JsonResponse
    {
        $slug = null;
        if ($request->content != null) {
            $slug = SlugService::createSlug(BlogArticle::class, 'slug', $request->content);
        }
        return response()->json(['slug' => $slug]);
    }

    /**
     * Show the form for creating a new blog article.
     */
    public function create(): View
    {
        $categories = BlogCategory::all();
        return view('admin.blog.articles.create', compact('categories'));
    }

    /**
     * Store a newly created blog article.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255', 'min:2'],
            'slug' => ['required', 'unique:blog_articles', 'alpha_dash'],
            'body' => ['required', 'string'],
            'category' => ['required', 'integer', 'exists:blog_categories,id'],
            'image' => ['required', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'short_description' => ['required', 'string', 'max:200', 'min:2'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrorsWithInput($validator);
        }

        try {
            $image = imageUpload($request->file('image'), 'images/blog/');
            $article = BlogArticle::create([
                'admin_id' => authAdmin()->id,
                'title' => $request->title,
                'slug' => $request->slug,
                'body' => $request->body,
                'image' => $image,
                'short_description' => $request->short_description,
                'blog_category_id' => $request->category,
            ]);

            return $this->createdRedirect('admin.blog.articles.edit', $article->id, 'Blog Article Created Successfully');
        } catch (\Exception $e) {
            return $this->errorBackWithInput($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified blog article.
     */
    public function edit(BlogArticle $article): View
    {
        $categories = BlogCategory::all();
        return view('admin.blog.articles.edit', compact('article', 'categories'));
    }

    /**
     * Update the specified blog article.
     */
    public function update(Request $request, BlogArticle $article): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'title' => ['required', 'string', 'max:255', 'min:2'],
            'slug' => ['required', 'alpha_dash', 'unique:blog_articles,slug,' . $article->id],
            'body' => ['required', 'string'],
            'category' => ['required', 'integer', 'exists:blog_categories,id'],
            'image' => ['nullable', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'short_description' => ['required', 'string', 'max:200', 'min:2'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        try {
            $image = ($request->has('image')) ? imageUpload($request->file('image'), 'images/blog/', null, null, $article->image) : $article->image;
            $article->update([
                'admin_id' => authAdmin()->id,
                'title' => $request->title,
                'slug' => $request->slug,
                'body' => $request->body,
                'image' => $image,
                'short_description' => $request->short_description,
                'blog_category_id' => $request->category,
            ]);

            return $this->updatedBack('Blog Article Updated Successfully');
        } catch (\Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Remove the specified blog article.
     */
    public function destroy(BlogArticle $article): JsonResponse
    {
        removeFile(public_path($article->image));
        $article->delete();
        return $this->successJson('Blog Article Deleted Successfully');
    }

    /**
     * Bulk delete blog articles.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                DB::beginTransaction();
                try {
                    $articles = BlogArticle::whereIn('id', $ids)->get();

                    foreach ($articles as $article) {
                        removeFile(public_path($article->image));
                        $article->delete();
                    }

                    DB::commit();

                    $message = translate(':count article(s) deleted successfully', ['count' => $articles->count()]);
                    return ['message' => $message, 'count' => $articles->count()];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            },
            BlogArticle::class,
            ':count article(s) deleted successfully',
            'Failed to delete articles'
        );
    }
}
