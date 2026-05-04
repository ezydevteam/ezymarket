<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserPanel;

use App\Http\Controllers\Controller;
use App\Enums\RefundStatus;
use App\Models\{Purchase, Refund, RefundReply, Support\TicketCategory};
use App\Traits\HandlesValidation;
use App\Facades\Notification;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{Request, JsonResponse};

class RefundController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of refunds related to the user (as buyer or seller).
     *
     * @return View|JsonResponse
     */
    public function index(): View|JsonResponse
    {
        $user = authUser();
        $query = Refund::where(function (Builder $query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('seller_id', $user->id);
        })->with('purchase.product');

        // Handle DataTables AJAX requests
        if (request()->ajax() && request()->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = request()->input('start', 0);
                $length = request()->input('length', 10);
                $refunds = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $refunds->map(fn($refund) => $this->formatRefundRow($refund));

                return response()->json([
                    'draw' => intval(request()->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $refunds = collect([]);
        $purchases = Purchase::where('user_id', $user->id)->active()->orderbyDesc('id')->get();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $hasRecords = $user->refunds()->exists();

        return theme_view('userpanel.refunds.index', compact('refunds', 'purchases', 'columns', 'filters', 'hasRecords'));
    }

    /**
     * Store a new refund request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = authUser();

        $windowStart = now()->subHours(12);
        $recentRefunds = Refund::where('user_id', $user->id)
            ->where('created_at', '>=', $windowStart)
            ->oldest()
            ->get();

        if ($recentRefunds->count() >= 2) {
            $nextAvailableAt = $recentRefunds->first()->created_at->addHours(12);
            $day = $nextAvailableAt->isToday() ? translate('today') : translate('tomorrow');

            return $this->errorJson(translate('You have reached the limit. You can submit request again :day at :time.', [
                'day' => $day,
                'time' => $nextAvailableAt->format('h:i A')
            ]));
        }

        $validator = $this->validateRequestJson($request, [
            'purchase' => ['required', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $purchase = Purchase::where('id', $request->purchase)
            ->where('user_id', $user->id)
            ->active()
            ->first();

        if (!$purchase) {
            return $this->errorJson('The selected purchase record is invalid or doesn\'t belong to you.');
        }

        $existingRefund = Refund::where('purchase_id', $purchase->id)
            ->pending()
            ->first();

        if ($existingRefund) {
            return $this->errorJson('You already have a pending refund request for this product.');
        }

        $product = $purchase->product;
        $seller = $product->seller;

        $refund = Refund::create([
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'subject' => $request->subject,
            'status' => RefundStatus::PENDING,
        ]);

        $refundReply = RefundReply::create([
            'refund_id' => $refund->id,
            'user_id' => $user->id,
            'message' => $request->reason,
        ]);

        Notification::sendRefundRequestNotification($refund, $refundReply);

        return $this->successJson('Your refund request has been submitted successfully.');
    }

    /**
     * Return the AJAX modal content for creating a refund request.
     *
     * @param Request $request
     * @return string
     */
    public function modalCreate(Request $request): string
    {
        $user = authUser();
        $purchases = Purchase::where('user_id', $user->id)->active()->orderbyDesc('id')->get();
        $selectedPurchaseId = $request->query('purchase');

        return theme_view('userpanel.refunds.partials.modals.modal_refund_create', compact('purchases', 'selectedPurchaseId'))->render();
    }

    /**
     * Show detailed view of a refund request.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->with(['purchase', 'user', 'seller', 'replies'])
            ->firstOrFail();

        $ticketCategories = TicketCategory::active()->get();

        return theme_view('userpanel.refunds.show', compact('refund', 'ticketCategories'));
    }

    /**
     * Add a reply to the refund request conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function reply(Request $request, int $id): JsonResponse
    {
        $validated = $this->validateRequestJson($request, [
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            })
            ->pending()
            ->firstOrFail();

        $refundReply = RefundReply::create([
            'refund_id' => $refund->id,
            'user_id' => $user->id,
            'message' => $request->reply,
        ]);

        Notification::sendRefundReplyNotification($refund, $refundReply);

        return $this->successJson('Your reply has been sent successfully');
    }

    /**
     * Decline a refund request (seller action).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function decline(Request $request, int $id): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'reason' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where('seller_id', $user->id)
            ->pending()
            ->firstOrFail();

        $refundReply = RefundReply::create([
            'refund_id' => $refund->id,
            'user_id' => $user->id,
            'message' => $request->reason,
        ]);

        $refund->decline();

        Notification::sendRefundStatusNotification($refund, $refundReply, 'declined');

        return $this->successJson('The refund request has been declined');
    }

    /**
     * Accept a refund request (seller action).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function accept(Request $request, int $id): JsonResponse
    {
        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where('seller_id', $user->id)
            ->pending()
            ->firstOrFail();

        $refund->accept();

        $refundReply = new RefundReply();

        Notification::sendRefundStatusNotification($refund, $refundReply, 'accepted');

        return $this->successJson('The refund request has been accepted');
    }

    /**
     * Cancel a refund request (user action).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$refund->canCancel()) {
            return $this->errorJson('This refund request cannot be cancelled');
        }

        $refund->cancel();

        return $this->successJson('Your refund request has been cancelled');
    }

    /**
     * Delete a refund request.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $user = authUser();

        $refund = Refund::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$refund->canDelete()) {
            return $this->errorJson('This refund request cannot be deleted');
        }

        $refund->delete();

        return $this->successJson('Your refund request has been deleted successfully');
    }

    /**
     * Apply filter and search logic to the refund query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableFilters(Builder $query): void
    {
        // Global Search
        if ($search = request()->input('search.value')) {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('id', 'like', "%{$cleanSearch}%")
                    ->orWhere('purchase_id', 'like', "%{$cleanSearch}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('purchase.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });

                // Search in Enum Labels (Refund Status)
                foreach (RefundStatus::cases() as $status) {
                    if (str_contains(strtolower($status->label()), strtolower($search))) {
                        $q->orWhere('status', $status->value);
                    }
                }
            });
        }

        // Column-specific AJAX filters
        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if (!$value) continue;

                if ($column == '2') { // Status Column
                    $query->where('status', $value);
                } elseif ($column == '3') { // Date Range Column
                    if (is_array($value)) {
                        if (!empty($value['from']) && strtotime($value['from'])) {
                            $query->whereDate('created_at', '>=', $value['from']);
                        }
                        if (!empty($value['to']) && strtotime($value['to'])) {
                            $query->whereDate('created_at', '<=', $value['to']);
                        }
                    }
                }
            }
        }

    }

    /**
     * Apply sorting to the refund query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableSorting(Builder $query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'purchase_id',
            1 => 'id',
            2 => 'status',
            3 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 3;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Format a single refund row for the DataTables AJAX response.
     *
     * @param Refund $refund
     * @return array
     */
    private function formatRefundRow(Refund $refund): array
    {
        return [
            'item' => theme_view('userpanel.refunds.partials.row_item', compact('refund'))->render(),
            'purchase' => theme_view('userpanel.refunds.partials.row_purchase', compact('refund'))->render(),
            'status' => theme_view('userpanel.refunds.partials.row_status', compact('refund'))->render(),
            'date' => '<span class="text-muted">' . dateFormat($refund->created_at) . '</span>',
            'actions' => theme_view('userpanel.refunds.partials.row_actions', compact('refund'))->render()
        ];
    }

    /**
     * Get columns for the DataTables
     *
     * @return array
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'item', 'name' => 'purchase_id', 'title' => translate('Product'), 'orderable' => true, 'searchable' => true],
            ['data' => 'purchase', 'name' => 'id', 'title' => translate('Refund Request'), 'orderable' => true, 'searchable' => true],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'date', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters for the DataTables
     *
     * @return array
     */
    private function getDataTableFilters(): array
    {
        return [
            [
                'type' => 'select',
                'column' => 2, // Status Column
                'label' => translate('Refund Status'),
                'options' => array_map(fn($case) => ['value' => $case->value, 'label' => $case->label()], RefundStatus::cases())
            ],
            [
                'type' => 'daterange',
                'column' => 3, // Date Range Column
                'label' => translate('Date Range')
            ]
        ];

    }
}
