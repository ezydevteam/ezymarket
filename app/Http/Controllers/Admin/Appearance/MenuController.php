<?php

namespace App\Http\Controllers\Admin\Appearance;

use App\Enums\Menu\{MenuLocation, MenuType, MenuStyle};
use App\Http\Controllers\Controller;
use App\Classes\BootstrapIcons;
use App\Models\Appearance\Menu;
use App\Models\Page;
use App\Models\Product\{ProductCategory, ProductSubCategory};
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Collection;

/**
 * MenuController
 *
 * Unified WordPress-style menu management controller.
 * Handles all menu locations (top, bottom, footer, mobile).
 */
class MenuController extends Controller
{
    use HandlesValidation;

    /**
     * Display menus with location filter.
     */
    public function index(Request $request): View
    {
        $location = $request->get('location', MenuLocation::TOP->value);
        $search = $request->get('search') ?? '';
        $data = $this->getViewData($location, $search);

        return view('admin.appearance.menus.index', compact('location', 'search') + $data);
    }

    /**
     * Update nested menu order via drag-drop.
     */
    public function nestable(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'ids' => ['required'],
            'location' => ['nullable', 'string'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();
        $ids = $validated['ids'];
        $data = is_string($ids) ? json_decode($ids, true) : $ids;
        $location = $validated['location'] ?? MenuLocation::TOP->value;

        if (!is_array($data) || empty($data)) {
            return $this->successJson('No changes to update');
        }

        try {
            $this->updateMenuOrder($data, null, $location);

            return $this->successJson('Menu order updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to update menu order', 500);
        }
    }

    /**
     * Update menu (AJAX).
     */
    public function update(Request $request, Menu $menu): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'menu_type' => ['required', 'string', 'in:' . implode(',', MenuType::values())],
            'menu_style' => ['nullable', 'string', 'in:' . implode(',', MenuStyle::values())],
            'location' => ['required', 'string', 'in:' . implode(',', MenuLocation::values())],
            'badge' => ['nullable', 'string', 'max:100'],
            'badge_color' => ['nullable', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:100'],
            'icon_color' => ['nullable', 'string', 'max:50'],
            'custom_class' => ['nullable', 'string', 'max:255'],
            'custom_html' => ['nullable', 'string'],
            'hide_name' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();

        $menu->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'menu_type' => $validated['menu_type'],
            'menu_style' => $validated['menu_style'] ?? null,
            'location' => $validated['location'],
            'badge' => $validated['badge'] ?? null,
            'badge_color' => $validated['badge_color'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'icon_color' => $validated['icon_color'] ?? null,
            'custom_class' => $validated['custom_class'] ?? null,
            'custom_html' => $validated['custom_html'] ?? null,
            'hide_name' => $request->has('hide_name'),
            'is_active' => $request->has('is_active'),
        ]);

        return $this->successJson('Menu updated successfully');
    }

    /**
     * Delete menu and its children.
     */
    public function destroy(Menu $menu): JsonResponse
    {
        $menu->children()->delete();
        $menu->delete();

        return $this->successJson('Menu deleted successfully');
    }


    /**
     * Bulk add menu items (AJAX).
     */
    public function bulkAdd(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.slug' => ['nullable', 'string', 'max:255'],
            'items.*.type' => ['required', 'string', 'in:page,category,subcategory,custom'],
            'location' => ['required', 'string', 'in:' . implode(',', MenuLocation::values())],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();
        $location = $validated['location'];
        $count = 0;
        $skipped = 0;

        foreach ($validated['items'] as $item) {
            // Skip if slug already exists in this location (exclude null/empty slugs from duplicate check)
            if (!empty($item['slug']) && Menu::where('location', $location)->where('slug', $item['slug'])->exists()) {
                $skipped++;
                continue;
            }

            $nextOrder = Menu::where('location', $location)->max('order_id') + 1;

            // Determine menu type: heading if no slug, external if URL, otherwise internal
            if (empty($item['slug'])) {
                $menuType = MenuType::HEADING->value;
            } elseif (str_starts_with($item['slug'], 'http://') || str_starts_with($item['slug'], 'https://')) {
                $menuType = MenuType::EXTERNAL->value;
            } else {
                $menuType = MenuType::INTERNAL->value;
            }

            Menu::create([
                'name' => $item['name'],
                'slug' => $item['slug'] ?? null,
                'menu_type' => $menuType,
                'location' => $location,
                'order_id' => $nextOrder,
                'is_active' => true,
            ]);

            $count++;
        }

        // Get updated menu items HTML for silent reload
        $html = view('admin.appearance.menus.partials.view', $this->getViewData($location))->render();

        if ($skipped > 0) {
            return $this->successJson(
                ':count menu items added, :skipped duplicates skipped',
                ['html' => $html],
                200,
                ['count' => $count, 'skipped' => $skipped]
            );
        }

        return $this->successJson(':count menu items added successfully', ['html' => $html], 200, ['count' => $count]);
    }

    /**
     * Bulk delete menus (AJAX).
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                // Delete children first, then parents
                Menu::whereIn('parent_id', $ids)->delete();
                return Menu::whereIn('id', $ids)->delete();
            },
            Menu::class,
            ':count menu(s) deleted successfully',
            'Failed to delete menus'
        );
    }

    /**
     * Get menus from a specific location for import modal (AJAX).
     */
    public function renderMenuList(Request $request): JsonResponse
    {
        $location = $request->get('location');

        if (!$location || !in_array($location, MenuLocation::values())) {
            return $this->errorJson('Invalid location');
        }

        $menus = $this->getMenusByLocation($location);

        $html = view('admin.appearance.menus.partials.import-menu-list', [
            'menus' => $menus,
            'location' => $location,
        ])->render();

        return $this->successJson('Menus loaded', ['html' => $html]);
    }

    /**
     * Import menus from another location (AJAX).
     */
    public function import(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'from_location' => ['required', 'string', 'in:' . implode(',', MenuLocation::values())],
            'to_location' => ['required', 'string', 'in:' . implode(',', MenuLocation::values())],
            'menu_ids' => ['required', 'array', 'min:1'],
            'menu_ids.*' => ['required', 'integer', 'exists:menus,id'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();
        $fromLocation = $validated['from_location'];
        $toLocation = $validated['to_location'];
        $menuIds = $validated['menu_ids'];

        if ($fromLocation === $toLocation) {
            return $this->errorJson('Cannot import from the same location');
        }

        $count = 0;
        $skipped = 0;

        // Get menus to import (only parent level, children will be handled recursively)
        $menusToImport = Menu::whereIn('id', $menuIds)
            ->where('location', $fromLocation)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        foreach ($menusToImport as $menu) {
            // Skip if slug already exists in target location (for menus with slugs)
            if (!empty($menu->slug)) {
                if (Menu::where('location', $toLocation)->where('slug', $menu->slug)->exists()) {
                    $skipped++;
                    continue;
                }
            } else {
                // For menus without slug (headings), check by name
                // Handle both null and empty string slugs
                if (Menu::where('location', $toLocation)
                    ->where('name', $menu->name)
                    ->where(function ($q) {
                        $q->whereNull('slug')->orWhere('slug', '');
                    })
                    ->exists()
                ) {
                    $skipped++;
                    continue;
                }
            }

            $this->duplicateMenu($menu, $toLocation);
            $count++;
        }

        // All duplicates - return error (no need to render HTML)
        if ($count === 0 && $skipped > 0) {
            return $this->errorJson('All selected menus already exist in this location');
        }

        // Get updated menu items HTML for silent reload
        $html = view('admin.appearance.menus.partials.view', $this->getViewData($toLocation))->render();

        // Some imported, some skipped
        if ($skipped > 0) {
            return $this->successJson(
                $count . ' menu(s) imported, ' . $skipped . ' duplicate(s) skipped',
                ['html' => $html]
            );
        }

        // All imported successfully
        return $this->successJson($count . ' menu(s) imported successfully', ['html' => $html]);
    }

    /**
     * Duplicate a menu and its children to a new location.
     */
    protected function duplicateMenu(Menu $menu, string $toLocation, ?int $parentId = null): Menu
    {
        $nextOrder = Menu::where('location', $toLocation)
            ->when($parentId, fn($q) => $q->where('parent_id', $parentId), fn($q) => $q->whereNull('parent_id'))
            ->max('order_id') + 1;

        $newMenu = Menu::create([
            'name' => $menu->name,
            'slug' => $menu->slug,
            'menu_type' => $menu->menu_type,
            'menu_style' => $menu->menu_style,
            'location' => $toLocation,
            'parent_id' => $parentId,
            'order_id' => $nextOrder,
            'badge' => $menu->badge,
            'badge_color' => $menu->badge_color,
            'icon' => $menu->icon,
            'icon_color' => $menu->icon_color,
            'custom_class' => $menu->custom_class,
            'custom_html' => $menu->custom_html,
            'hide_name' => $menu->hide_name,
            'is_active' => $menu->is_active,
        ]);

        // Recursively duplicate children
        foreach ($menu->children as $child) {
            $this->duplicateMenu($child, $toLocation, $newMenu->id);
        }

        return $newMenu;
    }

    /**
     * Get common view data for menu views.
     */
    protected function getViewData(string $location, ?string $search = null): array
    {
        $menus = $search
            ? $this->searchMenus($location, $search)
            : $this->getMenusByLocation($location);

        return [
            'location' => $location,
            'menus' => $menus,
        ] + $this->getMenuFormData();
    }

    /**
     * Get menus by location with children.
     */
    protected function getMenusByLocation(string $location): Collection
    {
        return Menu::location($location)
            ->parents()
            ->withOrderedChildren()
            ->byOrder()
            ->get();
    }

    /**
     * Search menus by name or slug.
     */
    protected function searchMenus(string $location, string $search): Collection
    {
        return Menu::location($location)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->withOrderedChildren()
            ->byOrder()
            ->get()
            ->filter(fn($menu) => $this->menuMatchesSearch($menu, $search));
    }

    /**
     * Check if menu or its children match search.
     */
    protected function menuMatchesSearch(Menu $menu, string $search): bool
    {
        $parentMatches = stripos($menu->name, $search) !== false
            || stripos($menu->slug, $search) !== false;

        $hasMatchingChild = $menu->children->contains(
            fn($child) => stripos($child->name, $search) !== false
                || stripos($child->slug, $search) !== false
        );

        return $parentMatches || $hasMatchingChild;
    }

    /**
     * Get common form data for views.
     */
    protected function getMenuFormData(): array
    {
        return [
            'locations' => Menu::getLocations(),
            'menuTypes' => Menu::getMenuTypes(),
            'menuStyles' => Menu::getMenuStyles(),
            'badgeColors' => Menu::getColorScheme(),
            'icons' => BootstrapIcons::all(true),
            'iconColors' => Menu::getColorScheme(),
            'pages' => Page::select('id', 'title', 'slug')->orderBy('title')->get(),
            'categories' => ProductCategory::select('id', 'name', 'slug')->orderBy('name')->get(),
            'subCategories' => ProductSubCategory::with('category:id,name,slug')
                ->select('id', 'name', 'slug', 'category_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Recursively update menu order.
     */
    protected function updateMenuOrder(array $data, ?int $parentId = null, ?string $location = null): void
    {
        foreach ($data as $index => $item) {
            $menu = Menu::find($item['id']);

            if ($menu) {
                $menu->update([
                    'order_id' => $index + 1,
                    'parent_id' => $parentId,
                    'location' => $location ?? $menu->location,
                ]);

                if (isset($item['children']) && is_array($item['children'])) {
                    $this->updateMenuOrder($item['children'], $menu->id, $location);
                }
            }
        }
    }
}
