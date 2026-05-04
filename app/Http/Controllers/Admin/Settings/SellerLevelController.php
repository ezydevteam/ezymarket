<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Traits\HandlesValidation;
use App\Models\SellerLevel;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;
use Exception;

class SellerLevelController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of seller levels.
     */
    public function index(): View
    {
        $sellerLevels = SellerLevel::query()->get();

        return view('admin.settings.seller-levels.index', compact('sellerLevels'));
    }

    /**
     * Store a newly created seller level in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:255', 'unique:seller_levels,name'],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:5120'],
            'fees' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_earnings' => ['required', 'numeric', 'min:0', 'unique:seller_levels,min_earnings'],
        ];

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $sellerLevel = new SellerLevel([
                'name' => $request->name,
                'min_earnings' => $request->min_earnings,
                'fees' => $request->fees,
            ]);

            if ($request->hasFile('icon')) {
                $sellerLevel->icon = imageUpload($request->file('icon'), 'seller-levels/', '128x128');
            }

            $sellerLevel->save();

            return $this->successJson('Seller level created successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the edit modal for the specified seller level (Ajax).
     */
    public function editModal(SellerLevel $sellerLevel): JsonResponse
    {
        return response()->json([
            'title' => translate('Edit Seller Level'),
            'content' => view('admin.settings.seller-levels.partials.edit-modal', compact('sellerLevel'))->render()
        ]);
    }

    /**
     * Update the specified seller level in storage.
     */
    public function update(Request $request, SellerLevel $sellerLevel): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:255', 'unique:seller_levels,name,' . $sellerLevel->id],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:5120'],
            'fees' => ['required', 'numeric', 'min:0', 'max:100'],
        ];

        if (!$sellerLevel->isDefault()) {
            $rules['min_earnings'] = ['required', 'numeric', 'min:0', 'unique:seller_levels,min_earnings,' . $sellerLevel->id];
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $data = [
                'name' => $request->name,
                'fees' => $request->fees,
            ];

            if (!$sellerLevel->isDefault()) {
                $data['min_earnings'] = $request->min_earnings;
            }

            if ($request->hasFile('icon')) {
                $data['icon'] = imageUpload($request->file('icon'), 'seller-levels/', '128x128', null, $sellerLevel->icon);
            }

            $sellerLevel->update($data);

            return $this->successJson('Seller level updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Remove the specified seller level from storage.
     */
    public function destroy(SellerLevel $sellerLevel): JsonResponse
    {
        if ($sellerLevel->isDefault()) {
            return $this->errorJson('Cannot delete default seller level');
        }

        $sellerLevel->deleteIcon();
        $sellerLevel->delete();

        return $this->successJson('Seller level deleted successfully');
    }

    /**
     * Bulk delete multiple seller levels.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $levels = SellerLevel::whereIn('id', $ids)
                    ->where('is_default', false)
                    ->get();

                if ($levels->isEmpty()) {
                    throw new Exception(translate('Default seller level cannot be deleted.'));
                }

                $deletedCount = 0;
                foreach ($levels as $level) {
                    if (!$level->isDefault()) {
                        $level->deleteIcon();
                        $level->delete();
                        $deletedCount++;
                    }
                }

                $skippedCount = count($ids) - $deletedCount;

                if ($skippedCount > 0) {
                    return [
                        'count' => $deletedCount,
                        'message' => translate(':count level(s) deleted successfully. :skipped default level(s) skipped', [
                            'count' => $deletedCount,
                            'skipped' => $skippedCount
                        ])
                    ];
                }

                return $deletedCount;
            },
            SellerLevel::class,
            ':count level(s) deleted successfully'
        );
    }
}
