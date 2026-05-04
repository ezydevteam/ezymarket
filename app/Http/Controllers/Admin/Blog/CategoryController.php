<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog\BlogCategory;
use App\Traits\HandlesValidation;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of blog categories.
     */
    public function index(Request $request): View
    {
        $query = BlogCategory::query();

        if ($request->filled('category')) {
            $query->where('id', $request->category);
        }

        $categories = $query->withCount('articles')->get();

        return view('admin.blog.categories.index', compact('categories'));
    }

    /**
     * Generate slug for blog category.
     */
    public function slug(Request $request): JsonResponse
    {
        $slug = null;
        if ($request->content != null) {
            $slug = SlugService::createSlug(BlogCategory::class, 'slug', $request->content);
        }
        return response()->json(['slug' => $slug]);
    }

    /**
     * Store a newly created blog category (AJAX only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'max:255', 'min:2'],
            'slug' => ['required', 'unique:blog_categories', 'alpha_dash'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorJson($validator);
        }

        $category = BlogCategory::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        if ($category) {
            return $this->successJson('Blog Category Created Successfully');
        }

        return $this->errorJson('An error occurred');
    }

    /**
     * Update the specified blog category (AJAX only).
     */
    public function update(Request $request, BlogCategory $category): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'max:255', 'min:2'],
            'slug' => ['required', 'alpha_dash', 'unique:blog_categories,slug,' . $category->id],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorJson($validator);
        }

        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return $this->successJson('Blog Category Updated Successfully');
    }

    /**
     * Remove the specified blog category.
     */
    public function destroy(BlogCategory $category): JsonResponse
    {
        if ($category->articles->count() > 0) {
            return $this->errorJson('The selected blog category has articles, it cannot be deleted');
        }

        $category->delete();
        return $this->successJson('Blog Category Deleted Successfully');
    }

    /**
     * Bulk delete blog categories
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                DB::beginTransaction();
                try {
                    $categories = BlogCategory::whereIn('id', $ids)->get();

                    // Check if any category has articles
                    foreach ($categories as $category) {
                        if ($category->articles()->exists()) {
                            throw new \Exception(translate('The selected blog category ":name" has articles, it cannot be deleted', ['name' => $category->name]));
                        }
                    }

                    // Delete all categories
                    $deletedCount = $categories->count();
                    foreach ($categories as $category) {
                        $category->delete();
                    }

                    DB::commit();

                    $message = translate(':count category(ies) deleted successfully', ['count' => $deletedCount]);
                    return ['message' => $message, 'count' => $deletedCount];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            },
            BlogCategory::class,
            ':count category(ies) deleted successfully',
            'Failed to delete categories'
        );
    }
}
