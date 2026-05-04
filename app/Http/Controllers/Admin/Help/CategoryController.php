<?php

namespace App\Http\Controllers\Admin\Help;

use App\Http\Controllers\Controller;
use App\Models\Knowledgebase\HelpCategory;
use App\Traits\{HandlesValidation, HandlesSorting};
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class CategoryController extends Controller
{
    use HandlesValidation, HandlesSorting;
    public function index(): View
    {
        $categories = HelpCategory::query();

        if (request()->filled('search')) {
            $searchTerm = '%' . request('search') . '%';
            $categories->where('name', 'like', $searchTerm);
        }

        $categories = $categories->get();

        return view('admin.help.categories.index', ['categories' => $categories]);
    }

    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, HelpCategory::class);
    }

    public function create(): View
    {
        return view('admin.help.categories.create');
    }

    public function slug(Request $request): JsonResponse
    {
        $slug = null;
        if ($request->content != null) {
            $slug = SlugService::createSlug(HelpCategory::class, 'slug', $request->content);
        }
        return response()->json(['slug' => $slug]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:help_categories', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrorsWithInput($validator);
        }

        $category = new HelpCategory();
        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->sort_id = (HelpCategory::count() + 1);
        $category->save();

        return $this->createdRedirect('admin.help.categories.edit', $category->id);
    }

    public function edit(HelpCategory $category): View
    {
        return view('admin.help.categories.edit', ['category' => $category]);
    }

    public function update(Request $request, HelpCategory $category): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:help_categories,slug,' . $category->id, 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrorsWithInput($validator);
        }

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->save();

        return $this->updatedBack();
    }

    public function destroy(HelpCategory $category): RedirectResponse
    {
        if ($category->articles->count() > 0) {
            return $this->errorBack('The selected category has articles, it cannot be deleted');
        }

        $category->delete();
        return $this->deletedBack();
    }
}


















