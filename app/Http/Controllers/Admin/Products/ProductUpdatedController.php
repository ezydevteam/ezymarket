<?php

namespace App\Http\Controllers\Admin\Products;

use App\Enums\Product\ProductHistoryTitle;
use App\Http\Controllers\Controller;
use App\Models\Product\{ProductUpdate, ProductCategory, ProductHistory};
use App\Facades\Notification;
use App\Traits\HandlesValidation;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};

class ProductUpdatedController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of product updates.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = ProductUpdate::with(['seller', 'product.category', 'product.subCategory']);

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $productUpdates = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $productUpdates->map(fn($update) => $this->formatProductUpdateRow($update));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data,
                ]);
            } catch (\Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $updatesCount = ProductUpdate::count();

        return view('admin.products.updated.index', compact('columns', 'filters', 'updatesCount'));
    }

    /**
     * Display the specified product update.
     *
     * @param Request $request
     * @param ProductUpdate $productUpdate
     * @return View|JsonResponse
     */
    public function show(Request $request, ProductUpdate $productUpdate): View|JsonResponse
    {
        $tab = $request->input('tab', 'details');

        // Map tabs to their partial views
        $tabsMap = [
            'details' => 'admin.products.updated.partials.details-content',
            'history' => 'admin.products.updated.partials.history-content',
            'actions' => 'admin.products.updated.partials.actions-content',
            'changelogs' => 'admin.products.updated.partials.changelogs-content',
        ];

        // Determine which partial to show
        $activePartial = $tabsMap[$tab] ?? 'admin.products.updated.partials.details-content';

        // Fetch data for tabs that need it
        $productHistories = [];
        $productChangelogs = [];

        if ($tab === 'history') {
            $productHistories = ProductHistory::where('product_id', $productUpdate->product->id)
                ->orderbyDesc('id')->paginate(10);
        } elseif ($tab === 'changelogs') {
            $productChangelogs = $productUpdate->product->changelogs()
                ->latest()->paginate(10);
        }

        $data = [
            'productUpdate' => $productUpdate,
            'productHistories' => $productHistories,
            'productChangelogs' => $productChangelogs,
            'activeTab' => $tab,
            'activePartial' => $activePartial,
        ];

        if ($request->ajax()) {
            return view($activePartial, $data);
        }

        return view('admin.products.updated.show', $data);
    }

    /**
     * Display product update history
     * Redirects to show page with history tab active.
     *
     * @param ProductUpdate $productUpdate
     * @return RedirectResponse
     */
    public function history(ProductUpdate $productUpdate): RedirectResponse
    {
        return $this->redirectToShow($productUpdate->id, 'history');
    }

    /**
     * Display product update actions page
     * Redirects to show page with actions tab active.
     *
     * @param ProductUpdate $productUpdate
     * @return RedirectResponse
     */
    public function actions(ProductUpdate $productUpdate): RedirectResponse
    {
        return $this->redirectToShow($productUpdate->id, 'actions');
    }

    /**
     * Process product update action (approve/reject).
     *
     * @param Request $request
     * @param ProductUpdate $productUpdate
     * @return JsonResponse
     */
    public function actionsUpdate(Request $request, ProductUpdate $productUpdate): JsonResponse
    {
        // Validate request
        $rules = [
            'action' => ['required', 'string', 'in:approve,reject'],
            'reason' => ['required_if:action,reject', 'nullable', 'string', 'max:1000'],
        ];

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $action = $request->action;

            return match ($action) {
                'approve' => $this->handleApprove($productUpdate),
                'reject' => $this->handleReject($productUpdate, $request->reason),
                default => $this->errorJson('Invalid action'),
            };
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Download product update main file.
     *
     * @param Request $request
     * @param ProductUpdate $productUpdate
     * @return mixed
     */
    public function download(ProductUpdate $productUpdate): mixed
    {
        abort_if(!$productUpdate->main_file, 404);

        try {
            $response = $productUpdate->download();

            if (isset($response->type) && $response->type == "error") {
                throw new \Exception($response->message);
            }

            return $response;
        } catch (\Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Delete a product update.
     *
     * @param ProductUpdate $productUpdate
     * @return JsonResponse
     */
    public function destroy(ProductUpdate $productUpdate): JsonResponse
    {
        try {
            $productUpdate->deleteFiles();
            $productUpdate->delete();

            $data = ProductUpdate::count() === 0
                ? ['redirect' => route('admin.products.updated.index')]
                : [];

            return $this->successJson('The update request has been deleted successfully', $data);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Bulk approve product updates.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkApprove(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $productUpdates = ProductUpdate::whereIn('id', $ids)->with('product')->get();
                $count = 0;

                foreach ($productUpdates as $productUpdate) {
                    $product = $productUpdate->product;
                    $productClone = clone $product;

                    // Update product fields from the update request
                    $this->applyProductUpdates($product, $productUpdate);

                    // Delete old files if new ones exist
                    $this->deleteOldProductFiles($productClone, $productUpdate);

                    // Create history record
                    ProductHistory::create([
                        'seller_id' => $product->seller_id,
                        'admin_id' => authAdmin()->id,
                        'product_id' => $product->id,
                        'title' => ProductHistoryTitle::UPDATE_APPROVED,
                    ]);

                    // Delete the update request
                    $productUpdate->delete();

                    // Send notification
                    Notification::sendProductUpdateStatusNotification($product, 'approved');

                    $count++;
                }

                return $count;
            },
            ProductUpdate::class,
            ':count update request(s) approved successfully'
        );
    }

    /**
     * Bulk reject product updates.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkReject(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids, Request $request) {
                $reason = $request->input('reason');
                $productUpdates = ProductUpdate::whereIn('id', $ids)->with('product')->get();
                $count = 0;

                foreach ($productUpdates as $productUpdate) {
                    $product = $productUpdate->product;

                    // Create history record with rejection reason
                    $productHistory = ProductHistory::create([
                        'seller_id' => $product->seller_id,
                        'admin_id' => authAdmin()->id,
                        'product_id' => $product->id,
                        'title' => ProductHistoryTitle::UPDATE_REJECTED,
                        'body' => $reason,
                    ]);

                    // Delete files and update request
                    $productUpdate->deleteFiles();
                    $productUpdate->delete();

                    // Send notification
                    Notification::sendProductUpdateStatusNotification($product, 'rejected', $productHistory);

                    $count++;
                }

                return $count;
            },
            ProductUpdate::class,
            ':count update request(s) rejected successfully'
        );
    }

    /**
     * Bulk delete product updates.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $productUpdates = ProductUpdate::whereIn('id', $ids)->get();
                foreach ($productUpdates as $productUpdate) {
                    $productUpdate->deleteFiles();
                    $productUpdate->delete();
                }
                return $productUpdates->count();
            },
            ProductUpdate::class,
            ':count update request(s) deleted successfully'
        );
    }

    /**
     * Handle product update approval.
     *
     * @param ProductUpdate $productUpdate
     * @return JsonResponse
     */
    private function handleApprove(ProductUpdate $productUpdate): JsonResponse
    {
        $product = $productUpdate->product;
        $productClone = clone $product;

        // Update product fields from the update request
        $this->applyProductUpdates($product, $productUpdate);

        // Delete old files if new ones exist
        $this->deleteOldProductFiles($productClone, $productUpdate);

        // Create history record
        ProductHistory::create([
            'seller_id' => $product->seller_id,
            'admin_id' => authAdmin()->id,
            'product_id' => $product->id,
            'title' => ProductHistoryTitle::UPDATE_APPROVED,
        ]);

        // Delete the update request
        $productUpdate->delete();

        // Send notification
        Notification::sendProductUpdateStatusNotification($product, 'approved');

        // Redirect to index if no more updates, otherwise reload page
        $data = ProductUpdate::count() === 0
            ? ['redirect' => route('admin.products.updated.index')]
            : [];

        return $this->successJson('The update request has been approved', $data);
    }

    /**
     * Handle product update rejection.
     *
     * @param ProductUpdate $productUpdate
     * @param string|null $reason
     * @return JsonResponse
     */
    private function handleReject(ProductUpdate $productUpdate, ?string $reason): JsonResponse
    {
        $product = $productUpdate->product;

        // Create history record with rejection reason
        $productHistory = ProductHistory::create([
            'seller_id' => $product->seller_id,
            'admin_id' => authAdmin()->id,
            'product_id' => $product->id,
            'title' => ProductHistoryTitle::UPDATE_REJECTED,
            'body' => $reason,
        ]);

        // Delete files and update request
        $productUpdate->deleteFiles();
        $productUpdate->delete();

        // Send notification
        Notification::sendProductUpdateStatusNotification($product, 'rejected', $productHistory);

        // Redirect to index if no more updates, otherwise reload page
        $data = ProductUpdate::count() === 0
            ? ['redirect' => route('admin.products.updated.index')]
            : [];

        return $this->successJson('The update request has been rejected', $data);
    }

    /**
     * Apply product updates from the update request.
     *
     * @param \App\Models\Product\Product $product
     * @param ProductUpdate $productUpdate
     * @return void
     */
    private function applyProductUpdates($product, ProductUpdate $productUpdate): void
    {
        // Always update these fields
        $product->name = $productUpdate->name;
        $product->description = $productUpdate->description;
        $product->options = $productUpdate->options;
        $product->version = $productUpdate->version;
        $product->demo_link = $productUpdate->demo_link;
        $product->tags = $productUpdate->tags;
        $product->purchasing_status = $productUpdate->purchasing_status;
        $product->is_free = $productUpdate->is_free;
        $product->is_supported = $productUpdate->is_supported;
        $product->support_instructions = $productUpdate->support_instructions;
        $product->regular_price_label = $productUpdate->regular_price_label;
        $product->extended_price_label = $productUpdate->extended_price_label;
        $product->regular_extra_features = $productUpdate->regular_extra_features;
        $product->extended_extra_features = $productUpdate->extended_extra_features;
        $product->has_custom_services = $productUpdate->has_custom_services;
        $product->custom_services = $productUpdate->custom_services;

        // Conditionally update price fields
        if ($productUpdate->regular_price) {
            $product->regular_price = $productUpdate->regular_price;
        }

        if ($productUpdate->extended_price) {
            $product->extended_price = $productUpdate->extended_price;
        }

        // Conditionally update file fields
        if ($productUpdate->preview_image) {
            $product->preview_image = $productUpdate->preview_image;
        }

        if ($productUpdate->preview_video) {
            $product->preview_video = $productUpdate->preview_video;
        }

        if ($productUpdate->preview_audio) {
            $product->preview_audio = $productUpdate->preview_audio;
        }

        if ($productUpdate->main_file) {
            $product->main_file = $productUpdate->main_file;
        }

        if ($productUpdate->gallery) {
            $product->gallery = $productUpdate->gallery;
        }

        $product->last_updated_at = Carbon::now();
        $product->update();
    }

    /**
     * Delete old product files if new ones exist.
     *
     * @param \App\Models\Product\Product $productClone
     * @param ProductUpdate $productUpdate
     * @return void
     */
    private function deleteOldProductFiles($productClone, ProductUpdate $productUpdate): void
    {
        if ($productUpdate->preview_image) {
            // Delete old preview image and its thumbnails
            $productClone->deletePreviewImage();
            thumbnailGenerator()->delete($productClone->preview_image);

            // Generate new thumbnails
            thumbnailGenerator()->generate($productUpdate->preview_image);
        }

        if ($productUpdate->preview_video) {
            $productClone->deletePreviewVideo();
        }

        if ($productUpdate->preview_audio) {
            $productClone->deletePreviewAudio();
        }

        if ($productUpdate->main_file) {
            $productClone->deleteMainFile();
        }

        if ($productUpdate->gallery) {
            $productClone->deleteGallery();
        }
    }

    /**
     * Redirect to show page with specified tab.
     *
     * @param int $id
     * @param string $tab
     * @return RedirectResponse
     */
    private function redirectToShow(int $id, string $tab = 'details'): RedirectResponse
    {
        return redirect()->route('admin.products.updated.show', ['productUpdate' => $id, 'tab' => $tab]);
    }

    /**
     * Apply filters to the query for DataTables.
     */
    private function applyDataTableFilters($query): void
    {
        $search = request()->input('search.value');
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('seller', function($sq) use ($search) {
                      $sq->where('username', 'like', "%{$search}%")
                        ->orWhere('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%");
                  });
            });
        }

        $filters = request()->input('filters');
        if (!empty($filters)) {
            foreach ($filters as $column => $value) {
                if (empty($value) && $value !== '0') continue;

                switch ($column) {
                    case '1': // Category (details column index)
                        $query->where('category_id', $value);
                        break;
                    case '4': // Submitted Date
                        if (is_array($value)) {
                            if (!empty($value['from']) && strtotime($value['from'])) {
                                $query->whereDate('created_at', '>=', $value['from']);
                            }
                            if (!empty($value['to']) && strtotime($value['to'])) {
                                $query->whereDate('created_at', '<=', $value['to']);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to the query for DataTables.
     */
    private function applyDataTableSorting($query): void
    {
        $order = request()->input('order.0');
        $sortColumns = [
            1 => 'name',
            3 => 'regular_price',
            4 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 4;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Format a single product update row for the DataTables AJAX response.
     */
    private function formatProductUpdateRow(ProductUpdate $update): array
    {
        return [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" value="' . $update->id . '">',
            'details' => view('admin.products.updated.draw.details', compact('update'))->render(),
            'seller' => view('admin.products.updated.draw.seller', compact('update'))->render(),
            'price' => view('admin.products.updated.draw.price', compact('update'))->render(),
            'changes' => view('admin.products.updated.draw.changes', compact('update'))->render(),
            'submitted_at' => view('admin.products.updated.draw.date', compact('update'))->render(),
            'actions' => view('admin.products.updated.draw.actions', compact('update'))->render(),
        ];
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'bulk', 'name' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'class' => 'no-sort no-export'],
            ['data' => 'details', 'name' => 'name', 'title' => translate('Product Details'), 'orderable' => true, 'searchable' => true],
            ['data' => 'seller', 'name' => 'seller_id', 'title' => translate('Seller'), 'orderable' => false, 'searchable' => false],
            ['data' => 'price', 'name' => 'regular_price', 'title' => translate('Price Changes'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'changes', 'name' => 'changes', 'title' => translate('Total Changes'), 'orderable' => false, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'submitted_at', 'name' => 'created_at', 'title' => translate('Submitted Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'no-sort no-export text-end'],
        ];
    }

    /**
     * Get filters configuration for the Datatable header.
     */
    private function getDataTableFilters(): array
    {
        $categories = ProductCategory::has('products')->get()->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray();

        return [
            [
                'type' => 'select',
                'column' => '1',
                'label' => translate('Category'),
                'options' => $categories
            ],
            [
                'type' => 'daterange',
                'column' => '4',
                'label' => translate('Submitted Date')
            ]
        ];
    }
}
