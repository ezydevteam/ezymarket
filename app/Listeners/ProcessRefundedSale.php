<?php

namespace App\Listeners;

use App\Enums\{
    PurchaseStatus,
    ReferralEarningStatus,
    SaleStatus,
    StatementType
};
use App\Events\SaleRefunded;
use App\Models\Financial\Statement;
use App\Models\{Purchase, User};
use App\Services\SupportPaymentService;

/**
 * Process refunded sale and handle all related reversals
 *
 * This listener handles:
 * - Sale status update
 * - Buyer refund with tax adjustments
 * - Seller balance deduction
 * - Purchase status update
 * - Statement creation for all parties
 * - Support payment refund
 * - Referral earning reversal
 * - Product statistics update
 * - Review cleanup if no active purchases remain
 */
class ProcessRefundedSale
{
    /**
     * Handle the sale refunded event
     */
    public function handle(SaleRefunded $event): void
    {
        $sale = $event->sale;

        // Load all related entities
        $buyer = $sale->user;
        $seller = $sale->seller;
        $product = $sale->product;
        $purchase = $sale->purchase;
        $buyerTax = $sale->buyer_tax;
        $sellerTax = $sale->seller_tax;
        $referralEarning = $sale->referralEarning;

        // Update sale status
        $sale->update(['status' => SaleStatus::REFUNDED]);

        // Reverse seller's earnings and statistics
        $this->reverseSellerBalances($seller, $sale);

        // Refund buyer
        $this->refundBuyer($buyer, $sale, $product, $purchase, $buyerTax);

        // Update purchase status
        $purchase->update(['status' => PurchaseStatus::REFUNDED]);

        // Process seller tax refund and create debit statement
        $this->processSellerRefund($seller, $sale, $product, $sellerTax);

        // Refund support payment
        app(SupportPaymentService::class)->refund($purchase);

        // Process referral earning reversal if exists
        if ($referralEarning) {
            $this->reverseReferralEarning($referralEarning);
        }

        // Update product statistics
        $this->updateProductStatistics($product, $sale, $sellerTax);

        // Cleanup buyer review if no active purchases remain
        $this->cleanupReviewIfNeeded($buyer, $product);
    }

    /**
     * Reverse seller's balance and statistics
     */
    protected function reverseSellerBalances(User $seller, $sale): void
    {
        $seller->decrement('balance', $sale->seller_earning);
        $seller->decrement('total_sales');
        $seller->decrement('total_sales_amount', $sale->price);
    }

    /**
     * Refund buyer including tax if applicable
     */
    protected function refundBuyer(User $buyer, $sale, $product, Purchase $purchase, $buyerTax): void
    {
        // Refund purchase amount
        $buyer->increment('balance', $sale->price);

        // Create buyer refund statement
        Statement::create([
            'user_id' => $buyer->id,
            'title' => translate('[Refund] Purchase #:id (:product_name)', [
                'id' => $purchase->id,
                'product_name' => $product->name,
            ]),
            'amount' => $sale->price,
            'total' => $sale->price,
            'type' => StatementType::CREDIT,
        ]);

        // Refund buyer tax if applicable
        if ($buyerTax) {
            $buyerTaxAmount = ($sale->price * $buyerTax->percentage) / 100;
            $buyer->increment('balance', $buyerTaxAmount);

            Statement::create([
                'user_id' => $buyer->id,
                'title' => translate('[Refund] :tax_name (:tax_rate%) Purchase #:id (:product_name)', [
                    'id' => $purchase->id,
                    'product_name' => $product->name,
                    'tax_name' => $buyerTax->name,
                    'tax_rate' => $buyerTax->percentage,
                ]),
                'amount' => $buyerTaxAmount,
                'total' => $buyerTaxAmount,
                'type' => StatementType::CREDIT,
            ]);
        }
    }

    /**
     * Process seller tax refund and create seller debit statement
     */
    protected function processSellerRefund(User $seller, $sale, $product, $sellerTax): void
    {
        $totalSellerEarning = $sellerTax
            ? ($sale->seller_earning + $sellerTax->amount)
            : $sale->seller_earning;

        // Refund seller tax if applicable
        if ($sellerTax) {
            Statement::create([
                'user_id' => $seller->id,
                'title' => translate('[Refund] :tax_name (:tax_rate%) Sale #:id (:product_name)', [
                    'id' => $sale->id,
                    'product_name' => $product->name,
                    'tax_name' => $sellerTax->name,
                    'tax_rate' => $sellerTax->percentage,
                ]),
                'amount' => $sellerTax->amount,
                'total' => $sellerTax->amount,
                'type' => StatementType::CREDIT,
            ]);
        }

        // Create seller debit statement for refunded sale
        Statement::create([
            'user_id' => $seller->id,
            'title' => translate('[Refund] Sale #:id (:product_name)', [
                'id' => $sale->id,
                'product_name' => $product->name,
            ]),
            'amount' => $totalSellerEarning,
            'buyer_fee' => $sale->buyer_fee,
            'seller_fee' => $sale->seller_fee,
            'total' => $sale->price,
            'type' => StatementType::DEBIT,
        ]);
    }

    /**
     * Reverse referral earning and update related balances
     */
    protected function reverseReferralEarning($referralEarning): void
    {
        $referredUser = $referralEarning->referral;
        $referralOwner = $referralEarning->seller;
        $referralAmount = $referralEarning->seller_earning;

        // Reverse referral statistics
        $referredUser->decrement('earnings', $referralAmount);
        $referralOwner->decrement('balance', $referralAmount);
        $referralOwner->decrement('total_referrals_earnings', $referralAmount);

        // Create debit statement for referral owner
        Statement::create([
            'user_id' => $referralOwner->id,
            'title' => translate('[Refund] Referral Earnings #:id', [
                'id' => $referralEarning->id,
            ]),
            'amount' => $referralAmount,
            'total' => $referralAmount,
            'type' => StatementType::DEBIT,
        ]);

        // Update referral earning status
        $referralEarning->update(['status' => ReferralEarningStatus::REFUNDED]);
    }

    /**
     * Update product sales statistics
     */
    protected function updateProductStatistics($product, $sale, $sellerTax): void
    {
        $totalSellerEarning = $sellerTax
            ? ($sale->seller_earning + $sellerTax->amount)
            : $sale->seller_earning;

        $product->decrement('total_sales');
        $product->decrement('total_sales_amount', $sale->price);
        $product->decrement('total_earnings', $totalSellerEarning);
    }

    /**
     * Delete buyer's review if no active purchases remain for this product
     */
    protected function cleanupReviewIfNeeded(User $buyer, $product): void
    {
        $existingReview = $product->reviews()->where('user_id', $buyer->id)->first();

        if ($existingReview) {
            $activePurchasesCount = Purchase::where('user_id', $buyer->id)
                ->where('product_id', $product->id)
                ->active()
                ->count();

            if ($activePurchasesCount === 0) {
                $existingReview->delete();
            }
        }
    }
}
