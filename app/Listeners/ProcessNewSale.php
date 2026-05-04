<?php

namespace App\Listeners;

use App\Enums\StatementType;
use App\Events\SaleCreated;
use App\Facades\Notification;
use App\Models\Financial\Statement;
use App\Models\{Purchase, User, ReferralEarning};
use App\Services\SupportPaymentService;
use Illuminate\Support\Str;

/**
 * Process new sale and handle all related operations
 *
 * This listener handles:
 * - Purchase record creation
 * - Seller balance and statistics update
 * - Buyer statement creation with tax deductions
 * - Seller statement creation with earnings
 * - Support payment processing
 * - Referral earnings if applicable
 * - Product statistics update
 * - Notification dispatch to buyer and seller
 */
class ProcessNewSale
{
    /**
     * Handle the sale created event
     */
    public function handle(SaleCreated $event): void
    {
        $sale = $event->sale;
        $transaction = $event->transaction;
        $supportPayment = $event->support;

        // Load all related entities
        $buyer = $sale->user;
        $seller = $sale->seller;
        $product = $sale->product;
        $buyerTax = $sale->buyer_tax;
        $sellerTax = $sale->seller_tax;

        // Check if this sale has already been processed (idempotency)
        if (Purchase::where('sale_id', $sale->id)->exists()) {
            return;
        }

        // Create purchase record
        $purchase = $this->createPurchase($buyer, $seller, $product, $sale);

        // Update seller's balance and statistics
        $this->updateSellerBalances($seller, $sale);

        // Create buyer statements (purchase + tax if applicable)
        $this->createBuyerStatements($buyer, $sale, $product, $purchase, $buyerTax);

        // Create seller statements (sale + tax if applicable)
        $this->createSellerStatements($seller, $sale, $product, $sellerTax);

        // Process support payment
        app(SupportPaymentService::class)->create($purchase, $transaction, $supportPayment);

        // Process referral earnings if enabled
        if (@settings('referral')->status) {
            $this->processReferralEarnings($buyer, $sale);
        }

        // Update product statistics
        $this->updateProductStatistics($product, $sale, $sellerTax);

        // Send notifications
        $this->sendNotifications($buyer, $seller, $product, $purchase, $sale);
    }

    /**
     * Create purchase record
     */
    protected function createPurchase(User $buyer, User $seller, $product, $sale): Purchase
    {
        return Purchase::create([
            'user_id' => $buyer->id,
            'seller_id' => $seller->id,
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'license_type' => $sale->license_type,
            'code' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Update seller's balance and statistics
     */
    protected function updateSellerBalances(User $seller, $sale): void
    {
        $seller->increment('balance', $sale->seller_earning);
        $seller->increment('total_sales');
        $seller->increment('total_sales_amount', $sale->price);
    }

    /**
     * Create buyer statements including purchase and tax
     */
    protected function createBuyerStatements(User $buyer, $sale, $product, Purchase $purchase, $buyerTax): void
    {
        // Create purchase debit statement
        Statement::create([
            'user_id' => $buyer->id,
            'title' => translate('[Purchase] #:id (:product_name)', [
                'id' => $purchase->id,
                'product_name' => $product->name,
            ]),
            'amount' => $sale->price,
            'total' => $sale->price,
            'type' => StatementType::DEBIT,
        ]);

        // Create buyer tax debit statement if applicable
        if ($buyerTax) {
            $buyerTaxAmount = ($sale->price * $buyerTax->percentage) / 100;

            Statement::create([
                'user_id' => $buyer->id,
                'title' => translate('[:tax_name (:tax_rate%)] Purchase #:id (:product_name)', [
                    'id' => $purchase->id,
                    'product_name' => $product->name,
                    'tax_name' => $buyerTax->name,
                    'tax_rate' => $buyerTax->percentage,
                ]),
                'amount' => $buyerTaxAmount,
                'total' => $buyerTaxAmount,
                'type' => StatementType::DEBIT,
            ]);
        }
    }

    /**
     * Create seller statements including sale and tax
     */
    protected function createSellerStatements(User $seller, $sale, $product, $sellerTax): void
    {
        $totalSellerEarning = $sellerTax
            ? ($sale->seller_earning + $sellerTax->amount)
            : $sale->seller_earning;

        // Create sale credit statement
        Statement::create([
            'user_id' => $seller->id,
            'title' => translate('[Sale] #:id (:product_name)', [
                'id' => $sale->id,
                'product_name' => $product->name,
            ]),
            'amount' => $sale->price,
            'buyer_fee' => $sale->buyer_fee,
            'seller_fee' => $sale->seller_fee,
            'total' => $totalSellerEarning,
            'type' => StatementType::CREDIT,
        ]);

        // Create seller tax debit statement if applicable
        if ($sellerTax) {
            Statement::create([
                'user_id' => $seller->id,
                'title' => translate('[:tax_name (:tax_rate%)] Sale #:id (:product_name)', [
                    'id' => $sale->id,
                    'product_name' => $product->name,
                    'tax_name' => $sellerTax->name,
                    'tax_rate' => $sellerTax->percentage,
                ]),
                'amount' => $sellerTax->amount,
                'total' => $sellerTax->amount,
                'type' => StatementType::DEBIT,
            ]);
        }
    }

    /**
     * Process referral earnings if buyer was referred
     */
    protected function processReferralEarnings(User $buyer, $sale): void
    {
        $referral = $buyer->referral;

        if (!$referral) {
            return;
        }

        $referralOwner = $referral->seller;
        $referralEarningAmount = ($sale->price * @settings('referral')->percentage) / 100;

        // Create referral earning record
        $referralEarning = ReferralEarning::create([
            'referral_id' => $referral->id,
            'seller_id' => $referralOwner->id,
            'sale_id' => $sale->id,
            'seller_earning' => $referralEarningAmount,
        ]);

        // Update referral statistics
        $referral->increment('earnings', $referralEarningAmount);
        $referralOwner->increment('balance', $referralEarningAmount);
        $referralOwner->increment('total_referrals_earnings', $referralEarningAmount);

        // Create referral earnings statement
        Statement::create([
            'user_id' => $referralOwner->id,
            'title' => translate('[Referral Earnings] #:id', [
                'id' => $referralEarning->id,
            ]),
            'amount' => $referralEarningAmount,
            'total' => $referralEarningAmount,
            'type' => StatementType::CREDIT,
        ]);
    }

    /**
     * Update product sales statistics
     */
    protected function updateProductStatistics($product, $sale, $sellerTax): void
    {
        $totalSellerEarning = $sellerTax
            ? ($sale->seller_earning + $sellerTax->amount)
            : $sale->seller_earning;

        $product->increment('total_sales');
        $product->increment('total_sales_amount', $sale->price);
        $product->increment('total_earnings', $totalSellerEarning);
    }

    /**
     * Send notifications to buyer and seller
     */
    protected function sendNotifications(User $buyer, User $seller, $product, Purchase $purchase, $sale): void
    {
        // Notify buyer of successful purchase
        Notification::sendPurchaseConfirmedNotification($buyer, $purchase);

        // Notify seller if they're not the buyer (self-purchase)
        if ($seller->id !== $buyer->id) {
            Notification::sendProductSoldNotification($seller, $product, $buyer);
            Notification::sendSalesEarningsNotification($seller, $sale);
        }
    }
}
