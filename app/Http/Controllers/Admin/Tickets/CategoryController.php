<?php

namespace App\Http\Controllers\Admin\Tickets;

use App\Http\Controllers\Controller;
use App\Models\Support\TicketCategory;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};

/**
 * Ticket Category Controller
 *
 * Handles CRUD operations for ticket categories including sorting,
 * filtering, and validation.
 */
class CategoryController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a listing of ticket categories with filtering options.
     *
     * @return View
     */
    public function index(): View
    {
        $query = TicketCategory::query()->withCount('tickets');

        if (request()->filled('category')) {
            $query->where('id', request('category'));
        }

        $categories = $query->get();

        return view('admin.tickets.categories.index', compact('categories'));
    }

    /**
     * Handle sortable table ajax request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, TicketCategory::class);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255', 'unique:ticket_categories'],
            'status' => ['required', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        TicketCategory::create([
            'name' => $request->name,
            'status' => $request->status === '1',
            'sort_id' => (TicketCategory::count() + 1),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category Created Successfully'
        ]);
    }

    /**
     * Update the specified category in storage.
     *
     * @param Request $request
     * @param TicketCategory $category
     * @return JsonResponse
     */
    public function update(Request $request, TicketCategory $category): JsonResponse
    {
        $validator = $this->validateRequest($request, [
            'name' => ['required', 'string', 'max:255', 'unique:ticket_categories,name,' . $category->id],
            'status' => ['required', 'in:0,1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $category->update([
            'name' => $request->name,
            'status' => $request->status === '1',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category Updated Successfully'
        ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param TicketCategory $category
     * @return RedirectResponse
     */
    public function destroy(TicketCategory $category): RedirectResponse
    {
        if ($category->tickets->count() > 0) {
            return $this->errorBack('The selected category has tickets, it cannot be deleted');
        }

        $category->delete();
        return $this->deletedBack();
    }

    /**
     * Bulk deactivate categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkInactive(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                return TicketCategory::whereIn('id', $ids)->update(['status' => false]);
            },
            TicketCategory::class,
            ':count categories deactivated successfully',
            'Failed to deactivate categories'
        );
    }

    /**
     * Bulk delete categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                // Check if any categories have tickets
                $categoriesWithTickets = TicketCategory::whereIn('id', $ids)
                    ->has('tickets')
                    ->count();

                if ($categoriesWithTickets > 0) {
                    throw new \Exception(translate('Some categories have tickets and cannot be deleted'));
                }

                return TicketCategory::whereIn('id', $ids)->delete();
            },
            TicketCategory::class,
            ':count categories deleted successfully',
            'Failed to delete categories'
        );
    }
}


















