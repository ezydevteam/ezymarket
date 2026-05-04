<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Enums\Page\{PageLayout, PageHeaderStyle};
use App\Traits\HandlesValidation;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of pages.
     */
    public function index(): View
    {
        $pages = Page::all();

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * Generate slug from title.
     */
    public function slug(Request $request): JsonResponse
    {
        $slug = $request->filled('content')
            ? SlugService::createSlug(Page::class, 'slug', $request->input('content'))
            : null;

        return response()->json(['slug' => $slug]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create(): View
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $validated = $validator->validated();
            $pageData = $this->preparePageData($validated, $request);

            $page = Page::create($pageData);
        } catch (\Exception $e) {
            return $this->errorJson('Error creating page: ' . $e->getMessage(), [], 500);
        }

        return $this->successJson('Page created successfully', ['redirect' => route('admin.pages.edit', $page->id)]);
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules($page));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $validated = $validator->validated();
            $pageData = $this->preparePageData($validated, $request, $page);

            $page->update($pageData);
        } catch (\Exception $e) {
            return $this->errorJson('Error updating page: ' . $e->getMessage(), [], 500);
        }

        return $this->successJson('Page updated successfully');
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return $this->successJson('Page deleted successfully', ['redirect' => route('admin.pages.index')]);
    }

    /**
     * Bulk delete pages.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $deletedCount = Page::whereIn('id', $ids)->delete();
                return $deletedCount;
            },
            Page::class,
            ':count page(s) deleted successfully',
            'Error deleting pages'
        );
    }

    /**
     * Get validation rules for page.
     *
     * @param Page|null $page Page instance for update (to exclude from unique check)
     * @return array
     */
    private function getValidationRules(?Page $page = null): array
    {
        return [
            'title' => ['required', 'string', 'block_patterns', 'min:2', 'max:255'],
            'content' => ['required', 'string', 'min:2'],
            'description' => ['nullable', 'string', 'block_patterns', 'max:255'],
            'layout' => ['required', 'string', Rule::in(array_column(PageLayout::cases(), 'value'))],
            'preview_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'],
            'header' => ['required', 'array'],
            'header.style' => ['required', 'string', Rule::in(array_column(PageHeaderStyle::cases(), 'value'))],
            'header.breadcrumb' => ['nullable', 'boolean'],
            'header.description' => ['nullable', 'boolean'],
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
        ];
    }

    /**
     * Prepare page data for create/update.
     *
     * @param array $validated Validated request data
     * @param Request $request The request instance
     * @param Page|null $page Page instance for update
     * @return array
     */
    private function preparePageData(array $validated, Request $request, ?Page $page = null): array
    {
        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'description' => sanitizeRichText($validated['description']),
            'layout' => $validated['layout'],
            'header' => [
                'style' => $validated['header']['style'],
                'breadcrumb' => isset($validated['header']['breadcrumb']) && $validated['header']['breadcrumb'] == '1',
                'description' => isset($validated['header']['description']) && $validated['header']['description'] == '1',
            ],
        ];

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = imageUpload(
                $request->file('preview_image'),
                'images/pages/',
                null,
                null,
                $page?->preview_image
            );
        } elseif (!$request->hasFile('preview_image') && !$request->filled('preview_image_current') && $page) {
            // No file uploaded and no current value = user removed the image
            if ($page->preview_image) {
                removeFile(public_path($page->preview_image));
            }
            $data['preview_image'] = null;
        } elseif ($page) {
            // Keep existing image if not uploading new one
            $data['preview_image'] = $page->preview_image;
        } else {
            $data['preview_image'] = null;
        }

        return $data;
    }
}
