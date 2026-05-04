<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles Sorting Trait
 *
 * Provides reusable sorting functionality for models with sortable tables.
 * Supports AJAX-based drag-and-drop sorting with sort_id field.
 */
trait HandlesSorting
{
    /**
     * Handle sortable table ajax request.
     *
     * Processes comma-separated IDs from drag-and-drop operations
     * and updates the sort_id for each model instance.
     *
     * @param Request $request The request containing 'ids' parameter
     * @param string $modelClass Fully qualified model class name (e.g., TicketCategory::class)
     * @return JsonResponse Success or error response
     */
    protected function handleSortable(Request $request, string $modelClass): JsonResponse
    {
        if (!$request->has('ids') || is_null($request->ids)) {
            return response()->json([
                'error' => translate('Failed to sort the table')
            ], 400);
        }

        $ids = explode(',', $request->ids);

        foreach ($ids as $sortOrder => $id) {
            $model = $modelClass::find($id);

            if ($model) {
                $model->sort_id = ($sortOrder + 1);
                $model->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => translate('Sorted Successfully')
        ]);
    }

    /**
     * Handle sortable with custom field name.
     *
     * Use this when your model uses a different field name instead of 'sort_id'.
     *
     * @param Request $request The request containing 'ids' parameter
     * @param string $modelClass Fully qualified model class name
     * @param string $sortField The field name to store sort order (default: 'sort_id')
     * @return JsonResponse Success or error response
     */
    protected function handleSortableWithField(Request $request, string $modelClass, string $sortField = 'sort_id'): JsonResponse
    {
        if (!$request->has('ids') || is_null($request->ids)) {
            return response()->json([
                'error' => translate('Failed to sort the table')
            ], 400);
        }

        $ids = explode(',', $request->ids);

        foreach ($ids as $sortOrder => $id) {
            $model = $modelClass::find($id);

            if ($model) {
                $model->{$sortField} = ($sortOrder + 1);
                $model->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => translate('Sorted Successfully')
        ]);
    }

    /**
     * Handle sortable with validation.
     *
     * Validates IDs exist in database before updating sort order.
     * Useful when you need to ensure all IDs are valid before making changes.
     *
     * @param Request $request The request containing 'ids' parameter
     * @param string $modelClass Fully qualified model class name
     * @param string $sortField The field name to store sort order (default: 'sort_id')
     * @return JsonResponse Success or error response
     */
    protected function handleSortableWithValidation(Request $request, string $modelClass, string $sortField = 'sort_id'): JsonResponse
    {
        if (!$request->has('ids') || is_null($request->ids)) {
            return response()->json([
                'error' => translate('Failed to sort the table')
            ], 400);
        }

        $ids = explode(',', $request->ids);

        // Validate all IDs exist
        $models = $modelClass::whereIn('id', $ids)->get();

        if ($models->count() !== count($ids)) {
            return response()->json([
                'error' => translate('Some items were not found')
            ], 404);
        }

        // Update sort order
        foreach ($ids as $sortOrder => $id) {
            $model = $models->firstWhere('id', $id);

            if ($model) {
                $model->{$sortField} = ($sortOrder + 1);
                $model->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => translate('Sorted Successfully')
        ]);
    }

    /**
     * Handle sortable through a relationship query.
     *
     * Use this when sorting models through a relationship (e.g., $user->badges()).
     *
     * @param Request $request The request containing 'ids' parameter
     * @param \Illuminate\Database\Eloquent\Relations\Relation $relationQuery The relationship query
     * @param string $sortField The field name to store sort order (default: 'sort_id')
     * @return JsonResponse Success or error response
     */
    protected function handleSortableRelationship(Request $request, $relationQuery, string $sortField = 'sort_id'): JsonResponse
    {
        if (!$request->has('ids') || is_null($request->ids)) {
            return response()->json([
                'error' => translate('Failed to sort the data')
            ], 400);
        }

        $ids = explode(',', $request->ids);

        foreach ($ids as $sortOrder => $id) {
            $model = $relationQuery->where('id', $id)->first();
            if ($model) {
                $model->{$sortField} = ($sortOrder + 1);
                $model->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => translate('Sorted Successfully')
        ]);
    }
}
