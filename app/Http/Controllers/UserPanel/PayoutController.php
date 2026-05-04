<?php

namespace App\Http\Controllers\UserPanel;

use App\Enums\PayoutStatus;
use App\Events\PayoutSubmitted;
use App\Http\Controllers\Controller;
use App\Models\Financial\{Payout, PayoutMethod};
use App\Facades\Notification;
use App\Traits\HandlesValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;
use Exception;


class PayoutController extends Controller
{
    use HandlesValidation;

    /**
     * Display the user's payout history.
     */
    public function index(): View|JsonResponse
    {
        $user = authUser();
        $query = Payout::where('seller_id', $user->id)->with('payoutMethod');

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
                $payouts = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $payouts->map(fn($payout) => $this->formatPayoutRow($payout));

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

        $payouts = collect([]);
        $counters = $this->getCounters($user);
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $hasRecords = Payout::where('seller_id', $user->id)->exists();

        return theme_view('userpanel.payouts.index', compact('counters', 'payouts', 'columns', 'filters', 'hasRecords'));
    }

    /**
     * Show payout details (AJAX content).
     */
    public function show(int $id): string
    {
        $user = authUser();
        $payout = Payout::where('id', $id)
            ->where('seller_id', $user->id)
            ->firstOrFail();

        return theme_view('userpanel.payouts.partials.modals.modal_details', compact('payout'))->render();
    }

    /**
     * Show payout withdrawal form (AJAX content).
     */
    public function modalPayout(): string
    {
        return theme_view('userpanel.payouts.partials.modals.modal_payout')->render();
    }

    /**
     * Store a new payout request.
     */
    public function store(Request $request): JsonResponse
    {
        $user = authUser();

        // Check if payout is enabled in settings
        if (!@settings('payout')->status) {
            return $this->errorJson('Payout requests are currently disabled. Please contact support for assistance.');
        }

        // Check for pending payouts
        $hasPendingPayout = Payout::where('seller_id', $user->id)
            ->pending()
            ->exists();

        if ($hasPendingPayout) {
            return $this->errorJson('You already have a pending payout request. Please wait for it to be processed.');
        }

        // Get the user's payout method
        $payoutMethod = $user->payoutMethod;

        if (!$payoutMethod) {
            return $this->errorJson('Please setup your payout method first');
        }

        if (!$user->hasPayoutAccount()) {
            return $this->errorJson('Please setup your payout account details first');
        }

        // Check monthly limit
        if ($monthlyLimit = $payoutMethod->monthly_limits) {
            $currentMonthPayouts = Payout::where('seller_id', $user->id)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->whereIn('status', [PayoutStatus::COMPLETED, PayoutStatus::APPROVED])
                ->count();

            if ($currentMonthPayouts >= $monthlyLimit) {
                return $this->errorJson('You have reached the maximum payout limit of :limit requests per month.', ['limit' => $monthlyLimit]);
            }
        }

        $requestedAmount = (float) $request->amount;
        $minimumAmount = $payoutMethod->min_amount;
        $maximumAmount = $payoutMethod->max_amount;

        // Check global minimum payout amount from settings
        $globalMinimum = (float) @settings('payout')->minimum ?? 0;

        // Determine effective minimum and maximum amounts
        $effectiveMinimum = (float) max($minimumAmount ?? 0, $globalMinimum);
        $effectiveMaximum = (float) ($maximumAmount > 0 ? min($maximumAmount, $user->balance) : $user->balance);

        // Validate the payout request
        try {
            $request->validate([
                'amount' => [
                    'required',
                    'numeric',
                    'min:' . $effectiveMinimum,
                    'max:' . $effectiveMaximum,
                ]
            ], [
                'amount.required' => translate('Amount is required'),
                'amount.numeric' => translate('Amount must be a number'),
                'amount.min' => translate('Amount must be at least :min', ['min' => getAmount($effectiveMinimum)]),
                'amount.max' => translate('Amount cannot exceed :max', ['max' => getAmount($effectiveMaximum)]),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorJson($e->validator->errors()->first());
        }

        // Validate amount against payout method limits (Double check)
        if (!$payoutMethod->isAmountValid($requestedAmount)) {
            if ($maximumAmount > 0 && $requestedAmount > $maximumAmount) {
                return $this->errorJson('Amount cannot exceed :max', ['max' => getAmount($maximumAmount)]);
            } else {
                return $this->errorJson('Amount must be at least :min', ['min' => getAmount($minimumAmount ?? 0)]);
            }
        }

        // Validate decimal format (only .5 allowed)
        if (!$this->isValidHalfDecimal($requestedAmount)) {
            return $this->errorJson('Only whole numbers or .5 decimals are allowed (e.g., 100, 100.5)');
        }

        // Calculate fees based on payout method configuration
        $fees = (float) $payoutMethod->calculateFees($requestedAmount);

        try {
            $payout = $this->createPayoutRecord($user, $payoutMethod, $requestedAmount, $fees);

            return $this->successJson('Your payout request of :amount has been submitted successfully', [
                'amount' => getAmount($requestedAmount)
            ]);
        } catch (\Exception $e) {
            logger()->error('Payout processing failed: ' . $e->getMessage(), ['user_id' => $user->id]);
            return $this->errorJson('An error occurred while processing your payout. Please try again later.');
        }
    }

    /**
     * Recall/cancell a pending payout request.
     */
    public function recall(int $id): JsonResponse
    {
        $user = authUser();
        $payout = Payout::where('id', $id)
            ->where('seller_id', $user->id)
            ->firstOrFail();

        if (!$payout->isPending()) {
            return $this->errorJson('Only pending payouts can be recalled.');
        }

        try {
            DB::transaction(function () use ($user, $payout) {
                // Update status to RECALLED
                $payout->status = PayoutStatus::RECALLED;
                $payout->save();

                // Refund the amount to user balance
                $user->increment('balance', (float) $payout->amount);
            });

            return $this->successJson('Payout request has been recalled successfully. Amount credited back to your balance.');
        } catch (\Exception $e) {
            return $this->errorJson('An error occurred while recalling your payout. Please try again later.');
        }
    }

    /**
     * Remove the payout record (Soft Delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $user = authUser();
        $payout = Payout::where('id', $id)
            ->where('seller_id', $user->id)
            ->firstOrFail();

        // Prevent deleting PENDING payouts (they should be cancelled first)
        if ($payout->isPending()) {
            return $this->errorJson('Pending payout requests cannot be deleted. Please recall it first.');
        }

        $payout->delete();

        return $this->successJson('Payout record has been deleted successfully.');
    }

    /**
     * Apply search, filters and sorting to the payout query.
     */
    private function getCounters($user): array
    {
        $pendingPayouts = Payout::where('seller_id', $user->id)
            ->pending()
            ->sum('amount');

        $totalPayouts = Payout::where('seller_id', $user->id)
            ->whereIn('status', [PayoutStatus::APPROVED, PayoutStatus::COMPLETED])
            ->sum('amount');

        return [
            'pending_payouts' => $pendingPayouts,
            'total_payouts' => $totalPayouts,
            'total_earnings' => $user->balance + $pendingPayouts + $totalPayouts,
        ];
    }

    /**
     * Apply sorting to the payout query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableSorting(Builder $query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'payout_method_id',
            1 => 'amount',
            2 => 'fees',
            3 => 'status',
            4 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 4;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        $query->orderBy($sortColumn, $sortDir);
    }

    /**
     * Apply columns-specific and AJAX filters to the payout query.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableFilters(Builder $query): void
    {
        // Global search
        if ($search = request()->input('search.value')) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', $searchTerm)
                    ->orWhere('method', 'like', $searchTerm)
                    ->orWhere('account', 'like', $searchTerm);
            });
        }

        // Column-specific AJAX filters
        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                if ($column == '0') { // Method Column
                    $query->where('payout_method_id', $value);
                } elseif ($column == '3') { // Status Column
                    $query->where('status', $value);
                } elseif ($column == '4') { // Date Range Column
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
     * Format a single payout row for the DataTables AJAX response.
     *
     * @param Payout $payout
     * @return array
     */
    private function formatPayoutRow(Payout $payout): array
    {
        return [
            'item' => theme_view('userpanel.payouts.partials.row_item', compact('payout'))->render(),
            'amount' => theme_view('userpanel.payouts.partials.row_amount', compact('payout'))->render(),
            'fees' => '<span class="small ' . ($payout->fees > 0 && $payout->isCompleted() ? 'text-danger' : 'text-muted') . '">' . $payout->fees_label . '</span>',
            'status' => theme_view('userpanel.payouts.partials.row_status', compact('payout'))->render(),
            'date' => '<span class="text-muted">' . dateFormat($payout->created_at) . '</span>',
            'actions' => theme_view('userpanel.payouts.partials.row_actions', compact('payout'))->render()
        ];
    }

    /**
     * Get columns configuration for DataTables.
     *
     * @return array
     */
    private function getDataTableColumns(): array
    {
        return [
            ['data' => 'item', 'name' => 'payout_method_id', 'title' => translate('Payout Method'), 'orderable' => true, 'searchable' => true],
            ['data' => 'amount', 'name' => 'amount', 'title' => translate('Amount'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'fees', 'name' => 'fees', 'title' => translate('Fees'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'status', 'name' => 'status', 'title' => translate('Status'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'date', 'name' => 'created_at', 'title' => translate('Date'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'actions', 'name' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    /**
     * Get filters configuration for DataTables.
     *
     * @return array
     */
    private function getDataTableFilters(): array
    {
        $payoutMethods = PayoutMethod::active()->get();
        $methodOptions = $payoutMethods->map(fn($m) => ['value' => $m->id, 'label' => $m->name])->toArray();

        return [
            [
                'type' => 'select',
                'column' => 0,
                'label' => translate('Payout Method'),
                'options' => $methodOptions
            ],
            [
                'type' => 'select',
                'column' => 3,
                'label' => translate('Status'),
                'options' => array_map(fn($k, $v) => ['value' => $k, 'label' => $v], array_keys(Payout::getStatusOptions()), array_values(Payout::getStatusOptions()))
            ],
            [
                'type' => 'daterange',
                'column' => 4,
                'label' => translate('Date Range')
            ]
        ];
    }

    /**
     * Create the payout record and deduct balance.
     */
    private function createPayoutRecord($user, $payoutMethod, $requestedAmount, $fees)
    {
        return DB::transaction(function () use ($user, $payoutMethod, $requestedAmount, $fees) {
            $payout = Payout::create([
                'seller_id' => $user->id,
                'payout_method_id' => $payoutMethod->id,
                'amount' => $requestedAmount,
                'fees' => $fees,
                'method' => $payoutMethod->name,
                'account' => $user->payout_account,
                'status' => PayoutStatus::PENDING,
            ]);

            // Deduct the full requested amount from user balance
            $user->decrement('balance', $requestedAmount);

            // Fire events and notifications
            event(new PayoutSubmitted($payout));
            Notification::sendPayoutSubmittedNotification($payout);

            return $payout;
        });
    }

    /**
     * Check if the amount is a valid half decimal (only .5 allowed).
     */
    private function isValidHalfDecimal(float $amount): bool
    {
        // Use a small epsilon to avoid floating point precision issues
        $decimalPart = $amount - floor($amount);

        return abs($decimalPart - 0.0) < 0.0001 || abs($decimalPart - 0.5) < 0.0001;
    }
}
