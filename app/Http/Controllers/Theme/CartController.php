<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use App\Traits\HandlesValidation;
use App\Enums\TransactionType;
use App\Models\Product\Product;
use App\Models\CartProduct;
use App\Models\Support\SupportPackage;
use App\Models\Financial\{Transaction, TransactionProduct};
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\{Facades\DB, Str};

class CartController extends Controller
{
    use HandlesValidation;
    /**
     * Display cart page
     *
     * @return View
     */
    public function index(): View
    {
        $cartProducts = CartProduct::forCurrentSession()
            ->with(['product', 'supportPackage'])
            ->orderbyDesc('id')
            ->paginate(12);

        $cartTotal = $cartProducts->sum(
            fn($cartProduct) => $cartProduct->getTotalAmountWithSupport()
        );

        $cartCount = $cartProducts->total();

        return theme_view('cart', compact('cartProducts', 'cartTotal', 'cartCount'));
    }

    /**
     * Add a product to the cart
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addProduct(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'license_type' => ['required', 'integer', 'min:1', 'max:2'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $product = Product::where('id', $request->product_id)
            ->approved()
            ->first();

        if (!$product) {
            return response()->json([
                'error' => translate('The chosen product is not available'),
            ]);
        }

        // Check if user is trying to buy their own product
        if (authUser()?->id == $product->seller_id) {
            return response()->json([
                'error' => translate('You cannot purchase your own product'),
            ]);
        }

        // Get support package ID if applicable
        $supportPackageId = $this->getSupportPackageId($request, $product);

        // Get or create session and user IDs
        [$sessionId, $userId] = $this->getSessionAndUserId();

        // Check if product already in cart
        $cartProduct = $this->findCartProduct($sessionId, $userId, $product->id, $request->license_type);

        if (!$cartProduct) {
            // Create new cart item
            CartProduct::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'product_id' => $product->id,
                'license_type' => $request->license_type,
                'support_package_id' => $supportPackageId,
            ]);
        } else {
            // Update existing cart item
            if ($cartProduct->quantity >= 50) {
                return response()->json([
                    'error' => translate('You have reached the limit for each product'),
                ]);
            }

            $cartProduct->update([
                'support_package_id' => $supportPackageId,
                'quantity' => $cartProduct->quantity + 1,
            ]);
        }

        return response()->json([
            'success' => translate('The product added to cart'),
        ]);
    }

    /**
     * Update a cart product
     *
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function updateProduct(Request $request, string $id): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'license_type' => ['required', 'integer', 'min:1', 'max:2'],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $cartProduct = CartProduct::where('id', $id)
            ->forCurrentSession()
            ->with('product')
            ->firstOrFail();

        // Get support package ID if applicable
        $supportPackageId = $this->getSupportPackageId($request, $cartProduct->product);

        // Check if another cart item exists with same product and license type
        $existingCartProduct = CartProduct::whereNot('id', $cartProduct->id)
            ->where('product_id', $cartProduct->product_id)
            ->where('license_type', $request->license_type)
            ->forCurrentSession()
            ->first();

        if ($existingCartProduct) {
            // Merge quantities into existing cart item
            $existingCartProduct->increment('quantity', $request->quantity);
            $cartProduct->delete();
        } else {
            // Update current cart item
            $cartProduct->update([
                'license_type' => $request->license_type,
                'quantity' => $request->quantity,
                'support_package_id' => $supportPackageId,
            ]);
        }

        return $this->successBack('The cart product has been updated');
    }

    /**
     * Remove a product from cart
     *
     * @param string $id
     * @return RedirectResponse
     */
    public function removeProduct(string $id): RedirectResponse
    {
        CartProduct::where('id', $id)
            ->forCurrentSession()
            ->firstOrFail()
            ->delete();

        return $this->successBack('Product removed from cart');
    }

    /**
     * Empty the cart
     *
     * @return RedirectResponse
     */
    public function empty(): RedirectResponse
    {
        CartProduct::forCurrentSession()->delete();

        return $this->successBack('Cart has been emptied');
    }

    /**
     * Process checkout
     *
     * @return RedirectResponse
     */
    public function checkout(): RedirectResponse
    {
        $cartProducts = CartProduct::forCurrentSession()
            ->with(['product', 'supportPackage'])
            ->get();

        if ($cartProducts->isEmpty()) {
            return $this->errorBack('Your cart is empty');
        }

        $user = authUser();
        if (!$user) {
            return $this->errorBack('Please login to continue');
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $transactionTotalAmount = $cartProducts->sum(
                fn($cartProduct) => $cartProduct->getTotalAmountWithSupport()
            );

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $transactionTotalAmount,
                'total' => $transactionTotalAmount,
                'type' => TransactionType::PURCHASE,
            ]);

            // Create transaction products
            foreach ($cartProducts as $cartProduct) {
                $this->createTransactionProduct($transaction, $cartProduct);
            }

            DB::commit();

            return redirect()->route('checkout.index', hash_encode($transaction->id));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBack('An error occurred. Please try again');
        }
    }

    /**
     * Get support package ID if applicable
     *
     * @param Request $request
     * @param Product $product
     * @return int|null
     */
    protected function getSupportPackageId(Request $request, Product $product): ?int
    {
        if (!@settings('product')->support_status || !$product->isSupported()) {
            return null;
        }

        if (!$request->has('support')) {
            return null;
        }

        $freePackage = freeSupportPackage();
        $paidPackage = $product->supportPackage;
        $requestedId = (int) $request->support;

        if ($freePackage && $requestedId === $freePackage->id) {
            return $freePackage->id;
        }

        if ($paidPackage && $requestedId === $paidPackage->id) {
            return $paidPackage->id;
        }

        return null;
    }

    /**
     * Get or create session and user IDs
     *
     * @return array [sessionId, userId]
     */
    protected function getSessionAndUserId(): array
    {
        $user = authUser();

        if ($user) {
            return [null, $user->id];
        }

        if (session()->has('session_id')) {
            $sessionId = session()->get('session_id');
        } else {
            $sessionId = sha1(Str::random(12) . time());
            session()->put('session_id', $sessionId);
        }

        return [$sessionId, null];
    }

    /**
     * Find existing cart product
     *
     * @param string|null $sessionId
     * @param int|null $userId
     * @param int $productId
     * @param int $licenseType
     * @return CartProduct|null
     */
    protected function findCartProduct(?string $sessionId, ?int $userId, int $productId, int $licenseType): ?CartProduct
    {
        $query = CartProduct::where('product_id', $productId)
            ->where('license_type', $licenseType);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        return $query->first();
    }

    protected function createTransactionProduct(Transaction $transaction, CartProduct $cartProduct): TransactionProduct
    {
        $product = $cartProduct->product;
        $price = $cartProduct->getUnitPrice();

        $support = null;
        if (@settings('product')->support_status && $product->isSupported()) {
            $supportPackage = $cartProduct->supportPackage;
            if ($supportPackage) {
                $supportPrice = $supportPackage->calculatePrice($price);
                $support = [
                    'name' => $supportPackage->name,
                    'title' => $supportPackage->title,
                    'days' => $supportPackage->days,
                    'percentage' => $supportPackage->getPercentage(),
                    'fixed' => $supportPackage->getFixed(),
                    'price' => $supportPrice,
                    'quantity' => $cartProduct->quantity,
                    'total' => ceil($supportPrice * $cartProduct->quantity),
                ];
            }
        }

        return TransactionProduct::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'license_type' => $cartProduct->license_type,
            'price' => $price,
            'quantity' => $cartProduct->quantity,
            'support' => $support,
            'total' => $cartProduct->getTotalAmountWithSupport(),
        ]);
    }
}
