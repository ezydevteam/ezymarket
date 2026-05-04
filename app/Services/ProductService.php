<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Financial\{Transaction, TransactionProduct};
use App\Models\Product\{Product, ProductReview, ProductReviewReply};
use App\Models\Support\SupportPackage;
use App\Models\User;
use App\Facades\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

/**
 * Service for handling product-related business actions like reviews, downloads, and purchases.
 */
readonly class ProductService
{
    /**
     * Store a product review and send notifications.
     */
    public function storeReview(Product $product, User $user, Request $request): ProductReview
    {
        $review = ProductReview::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            [
                'seller_id'  => $product->seller_id,
                'stars'      => (int) $request->input('review_stars'),
                'subject'    => (string) $request->input('subject'),
                'body'       => (string) $request->input('review'),
                'created_at' => Carbon::now(),
            ]
        );

        if ($review) {
            $review->reply?->delete();
            Notification::sendProductReviewNotification($product->seller, $review, $product);
        }

        return $review;
    }

    /**
     * Store a seller's reply to a product review.
     */
    public function storeReviewReply(Product $product, ProductReview $review, User $user, Request $request): ProductReviewReply
    {
        $reply = new ProductReviewReply();
        $reply->product_review_id = $review->id;
        $reply->user_id = $user->id;
        $reply->body = (string) $request->input('reply');
        $reply->save();

        Notification::sendProductReviewReplyNotification($review->user, $review, $reply, $product, $user);

        return $reply;
    }

    /**
     * Handle the Buy Now transaction creation logic.
     */
    public function handleBuyNow(Product $product, User $user, Request $request): Transaction
    {
        $licenseType = (int) $request->input('license_type');
        $price = ($licenseType === 2) ? $product->price->extended : $product->price->regular;
        $totalAmount = $price;

        $support = null;
        if (@settings('product')?->support_status && $product->isSupported() && $request->filled('support')) {
            $supportPackage = SupportPackage::findOrFail($request->input('support'));
            $supportPrice = $supportPackage->calculatePrice($price);

            $support = [
                'name'     => $supportPackage->name,
                'title'    => $supportPackage->title,
                'days'     => $supportPackage->days,
                'rate'     => $supportPackage->rate,
                'price'    => $supportPrice,
                'quantity' => 1,
                'total'    => $supportPrice,
            ];
            $totalAmount += $supportPrice;
        }

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $totalAmount;
        $transaction->total = $totalAmount;
        $transaction->type = TransactionType::PURCHASE;
        $transaction->save();

        $transactionProduct = new TransactionProduct();
        $transactionProduct->transaction_id = $transaction->id;
        $transactionProduct->product_id = $product->id;
        $transactionProduct->license_type = $licenseType;
        $transactionProduct->price = $price;
        $transactionProduct->support = $support;
        $transactionProduct->total = $price;
        $transactionProduct->save();

        return $transaction;
    }

    /**
     * Handle product download and increment download counters.
     */
    public function processDownload(Product $product, ?User $user = null): mixed
    {
        try {
            $response = $product->download();

            if (isset($response->type) && $response->type === 'error') {
                throw new Exception($response->message);
            }

            if ($product->isFree()) {
                $product->increment('free_downloads');
            } elseif ($product->isPremium() && $user) {
                $user->premium->increment('total_downloads');
            }

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
