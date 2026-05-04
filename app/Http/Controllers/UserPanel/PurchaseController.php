<?php

declare(strict_types=1);

namespace App\Http\Controllers\UserPanel;

use App\Http\Controllers\Controller;
use App\Enums\{LicenseType, TransactionType};
use App\Models\{Purchase, Financial\Transaction, Support\SupportPackage};
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{RedirectResponse, Request, JsonResponse};
use Symfony\Component\HttpFoundation\{BinaryFileResponse, StreamedResponse};

/**
 * User Panel Purchase Controller
 *
 * Manages user's purchases, downloads, license viewing, and support extensions.
 *
 * @package App\Http\Controllers\UserPanel
 */
class PurchaseController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of the authenticated user's active purchases
     *
     * @return View|JsonResponse
     */
    public function index(): View|JsonResponse
    {
        $user = authUser();
        $query = Purchase::where('user_id', $user->id)->active();

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
                $purchases = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $purchases->map(fn($purchase) => $this->formatPurchaseRow($purchase));

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

        $purchases = collect([]);
        $supportPackages = SupportPackage::notFree()->get();
        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $hasRecords = $user->purchases()->active()->exists();

        return theme_view('userpanel.purchases.index', compact('purchases', 'supportPackages', 'columns', 'filters', 'hasRecords'));
    }

    /**
     * Purchase initial support package for a product
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function purchaseSupport(Request $request, string $id): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'support' => ['required', 'integer', 'exists:support_packages,id'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        $userPurchase = Purchase::where('user_id', authUser()->id)
            ->where('id', $id)
            ->active()
            ->firstOrFail();

        $purchasedProduct = $userPurchase->product;
        $paidPackage = $purchasedProduct->supportPackage;

        if (!$paidPackage || (int) $request->support !== $paidPackage->id) {
            return $this->errorBack('The selected support package is not available for this product.');
        }

        $selectedSupportPackage = $paidPackage;

        $purchasedProduct = $userPurchase->product;

        if (!$this->validateProductAvailability($purchasedProduct)) {
            return back();
        }

        $supportTransaction = $this->createSupportTransaction(
            $userPurchase,
            $selectedSupportPackage,
            TransactionType::SUPPORT_PURCHASE
        );

        return redirect()->route('checkout.index', hash_encode($supportTransaction->id));
    }

    /**
     * Extend expired support for a purchased product
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function extendSupport(Request $request, string $id): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'support' => ['required', 'integer', 'exists:support_packages,id'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        $expiredPurchase = Purchase::where('user_id', authUser()->id)
            ->where('id', $id)
            ->active()
            ->firstOrFail();

        $purchasedProduct = $expiredPurchase->product;
        $paidPackage = $purchasedProduct->supportPackage;

        if (!$paidPackage || (int) $request->support !== $paidPackage->id) {
            return $this->errorBack('The selected support package is not available for this product.');
        }

        $selectedSupportPackage = $paidPackage;

        $purchasedProduct = $expiredPurchase->product;

        if (!$this->validateProductAvailability($purchasedProduct)) {
            return back();
        }

        $extensionTransaction = $this->createSupportTransaction(
            $expiredPurchase,
            $selectedSupportPackage,
            TransactionType::SUPPORT_EXTEND
        );

        return redirect()->route('checkout.index', hash_encode($extensionTransaction->id));
    }

    /**
     * Display the license information for a purchase
     *
     * @param string $id
     * @return View
     */
    public function showLicense(string $id): View
    {
        $purchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        return theme_view('userpanel.purchases.license', compact('purchase'));
    }

    /**
     * Download the purchased product file
     *
     * Enhanced with security headers to prevent browser warnings
     * and ensure safe file download experience.
     *
     * @param string $id
     * @return BinaryFileResponse|RedirectResponse|StreamedResponse
     */
    public function downloadProduct(string $id): BinaryFileResponse|RedirectResponse|StreamedResponse
    {
        $userPurchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $redirectUrl = route('user.purchase.index');
        if (!$this->isValidReferrer($redirectUrl)) {
            return redirect($redirectUrl);
        }

        $purchasedProduct = $userPurchase->product;

        try {
            $downloadResponse = $purchasedProduct->download();

            if (isset($downloadResponse->type) && $downloadResponse->type === "error") {
                throw new Exception($downloadResponse->message);
            }

            // Add security headers to prevent browser warnings
            $downloadResponse->headers->set('X-Content-Type-Options', 'nosniff');
            $downloadResponse->headers->set('X-Frame-Options', 'DENY');
            $downloadResponse->headers->set('X-XSS-Protection', '1; mode=block');
            $downloadResponse->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            // Ensure proper Content-Disposition for safe download
            if (!$downloadResponse->headers->has('Content-Disposition')) {
                $downloadResponse->headers->set('Content-Disposition', 'attachment');
            }

            $userPurchase->is_downloaded = true;
            $userPurchase->update();

            return $downloadResponse;
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }

    /**
     * Return the AJAX modal content for the purchase code.
     *
     * @param string $id
     * @return string
     */
    public function modalCode(string $id): string
    {
        $purchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        return theme_view('userpanel.purchases.partials.modals.modal_code', compact('purchase'))->render();
    }

    /**
     * Return the AJAX modal content for support purchase or renewal.
     *
     * @param string $id
     * @return string
     */
    public function modalSupport(string $id): string
    {
        $purchase = Purchase::where('id', $id)
            ->where('user_id', authUser()->id)
            ->active()
            ->firstOrFail();

        $supportPackages = SupportPackage::notFree()->get();

        return theme_view('userpanel.purchases.partials.modals.modal_support', compact('purchase', 'supportPackages'))->render();
    }

    /**
     * Apply filter and search logic to the purchase query for DataTables.
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
                $q->where('purchases.id', 'like', "%{$cleanSearch}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });

                // Search in Enum Labels (License Type)
                foreach (LicenseType::cases() as $license) {
                    if (str_contains(strtolower($license->label()), strtolower($search)) ||
                        str_contains(strtolower($license->shortLabel()), strtolower($search))) {
                        $q->orWhere('license_type', $license->value);
                    }
                }
            });
        }

        // Column-specific AJAX filters
        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if (!$value) continue;

                if ($column == '1') { // License Column
                    $query->where('purchases.license_type', $value);
                } elseif ($column == '2') { // Support Column
                    if ($value === 'active') {
                        $query->supportActive();
                    } elseif ($value === 'expired') {
                        $query->supportExpired();
                    }
                } elseif ($column == '3') { // Date Range Column
                    if (is_array($value)) {
                        if (!empty($value['from']) && strtotime($value['from'])) {
                            $query->whereDate('purchases.created_at', '>=', $value['from']);
                        }
                        if (!empty($value['to']) && strtotime($value['to'])) {
                            $query->whereDate('purchases.created_at', '<=', $value['to']);
                        }
                    }
                }
            }
        }
    }

    /**
     * Apply sorting to the purchase query for DataTables.
     *
     * @param Builder $query
     * @return void
     */
    private function applyDataTableSorting(Builder $query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            0 => 'product_id',
            1 => 'license_type',
            2 => 'support_expiry_at',
            3 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 3;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        if ($sortColumn === 'product_id') {
            $query->join('products', 'purchases.product_id', '=', 'products.id')
                ->select('purchases.*')
                ->orderBy('products.name', $sortDir);
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }
    }

    /**
     * Format a single purchase row for the DataTables AJAX response.
     *
     * @param Purchase $purchase
     * @return array
     */
    private function formatPurchaseRow(Purchase $purchase): array
    {
        return [
            'item' => theme_view('userpanel.purchases.partials.row_item', compact('purchase'))->render(),
            'license' => theme_view('userpanel.purchases.partials.row_license', compact('purchase'))->render(),
            'support' => theme_view('userpanel.purchases.partials.row_support', compact('purchase'))->render(),
            'date' => '<span class="text-gray-700 small">' . dateFormat($purchase->created_at) . '</span>',
            'actions' => theme_view('userpanel.purchases.partials.row_actions', compact('purchase'))->render()
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
            ['data' => 'item', 'name' => 'product_id', 'title' => translate('Product'), 'orderable' => true, 'searchable' => true],
            ['data' => 'license', 'name' => 'license_type', 'title' => translate('License'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
            ['data' => 'support', 'name' => 'support_expiry_at', 'title' => translate('Support'), 'orderable' => true, 'searchable' => false, 'class' => 'text-center'],
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
                'column' => 1,
                'label' => translate('License Type'),
                'options' => array_map(fn($case) => ['value' => $case->value, 'label' => $case->label()], LicenseType::cases())
            ],
            [
                'type' => 'select',
                'column' => 2,
                'label' => translate('Support Status'),
                'options' => [
                    ['value' => 'active', 'label' => translate('Active Support')],
                    ['value' => 'expired', 'label' => translate('Expired Support')]
                ]
            ],
            [
                'type' => 'daterange',
                'column' => 3,
                'label' => translate('Date Range')
            ]
        ];
    }

    /**
     * Validate if product is available for purchase and support
     *
     * @param mixed $product
     * @return bool
     */
    private function validateProductAvailability($product): bool
    {
        if (!$product->isPurchasingEnabled()) {
            toastr()->error(translate('The product is not available for purchase'));
            return false;
        }

        if (!$product->isSupported()) {
            toastr()->error(translate('The product is not supported by the seller'));
            return false;
        }

        return true;
    }

    /**
     * Create a support transaction for purchase or extension
     * @param Purchase $purchase
     * @param SupportPackage $supportPackage
     * @param TransactionType $transactionType
     * @return Transaction
     */
    private function createSupportTransaction(
        Purchase $purchase,
        SupportPackage $supportPackage,
        TransactionType $transactionType
    ): Transaction {
        $purchasedProduct = $purchase->product;
        $baseProductPrice = $purchase->isRegularLicense()
            ? $purchasedProduct->price->regular
            : $purchasedProduct->price->extended;

        $calculatedSupportAmount = $supportPackage->calculatePrice((float) $baseProductPrice);

        $supportDetails = [
            'name' => $supportPackage->name,
            'title' => $supportPackage->title,
            'days' => $supportPackage->days,
            'percentage' => $supportPackage->getPercentage(),
            'fixed' => $supportPackage->getFixed(),
            'price' => round($calculatedSupportAmount, 2),
            'quantity' => 1,
            'total' => round($calculatedSupportAmount, 2),
        ];

        $newTransaction = new Transaction();
        $newTransaction->user_id = authUser()->id;
        $newTransaction->amount = $calculatedSupportAmount;
        $newTransaction->total = $calculatedSupportAmount;
        $newTransaction->support = (object) $supportDetails;
        $newTransaction->purchase_id = $purchase->id;
        $newTransaction->type = $transactionType;
        $newTransaction->save();

        return $newTransaction;
    }

    /**
     * Validate if the request came from a valid referrer URL
     *
     * Ensures downloads are only initiated from the purchase index page
     * to prevent direct link abuse and unauthorized access attempts.
     *
     * @param string $expectedUrl
     * @return bool
     */
    private function isValidReferrer(string $expectedUrl): bool
    {
        $referrerUrl = request()->server('HTTP_REFERER');

        if (empty($referrerUrl)) {
            return false;
        }

        if (filter_var($referrerUrl, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $referrerUrlParts = parse_url($referrerUrl);
        $expectedUrlParts = parse_url($expectedUrl);

        if (!isset($referrerUrlParts['host']) || !isset($expectedUrlParts['host'])) {
            return false;
        }

        $referrerHost = strtolower($referrerUrlParts['host']);
        $expectedHost = strtolower($expectedUrlParts['host']);

        if ($referrerHost !== $expectedHost) {
            return false;
        }

        return true;
    }
}
