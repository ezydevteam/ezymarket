<?php

namespace App\Http\Controllers\Admin\Appearance;

use App\Http\Controllers\Controller;
use App\Enums\Widget\WidgetArea;
use App\Models\Appearance\{Widget, WidgetInstance};
use App\Traits\HandlesValidation;
use App\Widgets\WidgetManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};

/**
 * WidgetController
 *
 * WordPress-style widget management controller.
 * Handles widget areas (enum-based), instances, and drag-drop ordering.
 */
class WidgetController extends Controller
{
    use HandlesValidation;

    public function __construct(
        protected WidgetManager $widgetManager
    ) {}

    /**
     * Display widget management page.
     */
    public function index(): View
    {
        $widgets = Widget::active()->byTitle()->get();
        $areas = WidgetArea::cases();

        // Get instances grouped by area
        $instancesByArea = [];
        foreach ($areas as $area) {
            $instancesByArea[$area->value] = WidgetInstance::where('area', $area->value)
                ->orderBy('order_id')
                ->with('widget')
                ->get();
        }

        return view('admin.appearance.widgets.index', compact('widgets', 'areas', 'instancesByArea'));
    }

    /**
     * Store a new widget instance (add widget to area).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'widget_id' => ['required', 'exists:widgets,id'],
            'area' => ['required', 'string', 'in:' . implode(',', WidgetArea::values())],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();

        // Get next order position
        $nextOrder = WidgetInstance::where('area', $validated['area'])
            ->max('order_id') + 1;

        $instance = WidgetInstance::create([
            'widget_id' => $validated['widget_id'],
            'area' => $validated['area'],
            'title' => $validated['title'] ?? null,
            'settings' => [],
            'order_id' => $nextOrder,
            'is_active' => true,
        ]);

        $instance->load('widget');

        return $this->successJson('Widget added successfully', [
            'instance' => $instance,
            'html' => view('admin.appearance.widgets.partials.widget-item', compact('instance'))->render(),
        ]);
    }

    /**
     * Update widget instance (AJAX).
     */
    public function update(Request $request, WidgetInstance $instance): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        // Build settings from all form fields except system fields
        $excludeFields = ['_token', '_method', 'title', 'gallery_images', 'existing_images', 'removed_images'];
        $settings = collect($request->all())
            ->except($excludeFields)
            ->toArray();

        // Handle file uploads for image fields (single files)
        foreach ($request->allFiles() as $fieldName => $file) {
            // Skip gallery_images array - handled separately
            if ($fieldName === 'gallery_images') continue;

            if ($file instanceof UploadedFile && $file->isValid()) {
                $oldPath = $instance->settings[$fieldName] ?? null;
                $path = storageFileUpload($file, 'widgets/', 'public', null, $oldPath);
                $settings[$fieldName] = $path;
            }
        }

        // Handle gallery images (multiple files)
        if ($request->hasFile('gallery_images') || $request->has('existing_images') || $request->has('removed_images')) {
            $settings['images'] = $this->handleGalleryUpload($request);
        }

        $instance->update([
            'title' => $validated['title'] ?: null,
            'settings' => array_merge($instance->settings ?? [], $settings),
        ]);

        return $this->successJson('Widget updated successfully', [
            'instance' => $instance->fresh()->load('widget'),
        ]);
    }

    /**
     * Delete widget instance.
     */
    public function destroy(WidgetInstance $instance): JsonResponse
    {
        // Check if widget is deletable
        $widgetClass = $instance->widget?->getWidgetInstance();
        if ($widgetClass && !$widgetClass->isDeletable()) {
            return $this->errorJson('This widget cannot be deleted. You can only disable it.');
        }

        $instance->delete();

        return $this->successJson('Widget removed successfully');
    }

    /**
     * Update widget order via drag-drop (AJAX).
     */
    public function sortable(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:widget_instances,id'],
            'items.*.order' => ['required', 'integer', 'min:0'],
            'items.*.area' => ['required', 'string', 'in:' . implode(',', WidgetArea::values())],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();
        $affectedAreas = [];

        foreach ($validated['items'] as $item) {
            WidgetInstance::where('id', $item['id'])->update([
                'order_id' => $item['order'],
                'area' => $item['area'],
            ]);
            $affectedAreas[] = $item['area'];
        }

        // Clear cache for affected areas
        foreach (array_unique($affectedAreas) as $area) {
            WidgetInstance::clearCache($area);
        }

        return $this->successJson('Widget order updated successfully');
    }

    /**
     * Get widget instance form (AJAX).
     */
    public function instance(WidgetInstance $instance): JsonResponse
    {
        $widget = $instance->widget?->getWidgetInstance();

        if (!$widget) {
            return $this->errorJson('Widget type not found');
        }

        return response()->json([
            'success' => true,
            'html' => $widget->renderSettingsForm($instance),
            'instance' => $instance,
        ]);
    }

    /**
     * Toggle widget active status (AJAX).
     */
    public function toggle(WidgetInstance $instance): JsonResponse
    {
        $instance->update(['is_active' => !$instance->is_active]);

        return $this->successJson(
            $instance->is_active ? 'Widget activated' : 'Widget deactivated',
            ['is_active' => $instance->is_active]
        );
    }

    /**
     * Handle gallery/multi-image uploads.
     */
    private function handleGalleryUpload(Request $request, int $maxImages = 8): array
    {
        $images = [];

        // Keep existing images that weren't removed
        $existingImages = $request->input('existing_images', []);
        $removedImages = $request->input('removed_images', []);

        if (is_array($existingImages)) {
            foreach ($existingImages as $imagePath) {
                if (!empty($imagePath) && !in_array($imagePath, $removedImages)) {
                    $images[] = $imagePath;
                }
            }
        }

        // Delete removed images from storage
        if (is_array($removedImages)) {
            foreach ($removedImages as $imagePath) {
                if (!empty($imagePath)) {
                    removeFileFromStorage($imagePath, 'public');
                }
            }
        }

        // Upload new images
        if ($request->hasFile('gallery_images')) {
            $newImages = $request->file('gallery_images');
            if (!is_array($newImages)) {
                $newImages = [$newImages];
            }
            foreach ($newImages as $image) {
                if ($image instanceof UploadedFile && $image->isValid() && count($images) < $maxImages) {
                    $path = storageFileUpload($image, 'widgets/', 'public');
                    if ($path) {
                        $images[] = $path;
                    }
                }
            }
        }

        return $images;
    }
}
