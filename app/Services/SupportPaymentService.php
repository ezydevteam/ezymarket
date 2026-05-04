<?php

namespace App\Services;

use App\Enums\StatementType;
use App\Enums\SupportEarningStatus;
use App\Models\Purchase;
use App\Models\Financial\SellerTax;
use App\Models\Financial\Statement;
use App\Models\Support\SupportEarning;
use App\Models\Financial\Transaction;
use App\Models\User;
use Carbon\Carbon;

/**
 * Support Payment Service for EasyMarket
 *
 * Manages the financial flow for product support packages:
 * - Creates support earnings when purchased
 * - Handles refunds for cancelled support
 * - Processes support extensions
 * - Manages tax calculations (buyer and seller)
 * - Creates financial statements for all parties
 *
 * Financial Flow:
 * 1. Buyer pays support price + buyer tax
 * 2. Seller receives support price - seller fee - seller tax
 * 3. Platform collects seller fees
 * 4. Tax authorities collect buyer and seller taxes
 *
 * Support Types:
 * - Initial purchase support
 * - Support extension (additional days)
 * - Support refund (if purchase refunded)
 * - Support cancellation (if purchase cancelled)
 *
 * @see App\Models\SupportEarning
 * @see App\Models\Statement
 */
class SupportPaymentService
{
    /**
     * Create a new support earning record
     *
     * This method is called when:
     * - A product is purchased with support
     * - Support is extended for an existing purchase
     * - A support payment is completed
     *
     * Process:
     * 1. Calculate seller fees and earnings
     * 2. Calculate buyer and seller taxes
     * 3. Create support earning record
     * 4. Update seller balance
     * 5. Create financial statements
     * 6. Set/extend support expiry date
     *
     * @param Purchase $purchase The purchase record
     * @param Transaction $trx The transaction record
     * @param object|null $support Support package details (name, title, days, price)
     * @return void
     */
    public function create(Purchase $purchase, Transaction $trx, ?object $support = null): void
    {
        // Check if support feature is enabled and support package exists
        if (!@settings('product')->support_status || !$support) {
            return;
        }

        $product = $purchase->product;
        $seller = $purchase->seller;
        $user = $purchase->user;
        $supportPrice = $support->price;

        // Calculate support expiry date
        $expiryDate = Carbon::now()->addDays($support->days);

        // Only process if support has a price (free support doesn't need financial records)
        if ($supportPrice > 0) {

            // Calculate seller fees based on their premium level
            $sellerFeesPercentage = $seller->level->fees;
            $sellerFeesAmount = $sellerFeesPercentage > 0
                ? ($supportPrice * $sellerFeesPercentage) / 100
                : 0;
            $sellerEarningAmount = $sellerFeesAmount > 0
                ? ($supportPrice - $sellerFeesAmount)
                : $supportPrice;

            // Calculate taxes
            $sellerTax = $this->calculateSellerTax($seller, $sellerEarningAmount, $user->address['country'] ?? null);
            $buyerTax = $this->calculateBuyerTax($trx, $supportPrice);

            // Final seller earning after taxes
            $sellerEarning = $sellerTax
                ? ($sellerEarningAmount - $sellerTax['amount'])
                : $sellerEarningAmount;

            // Create support earning record
            $supportEarning = new SupportEarning();
            $supportEarning->seller_id = $seller->id;
            $supportEarning->purchase_id = $purchase->id;
            $supportEarning->name = $support->name;
            $supportEarning->title = $support->title;
            $supportEarning->days = $support->days;
            $supportEarning->price = $supportPrice;
            $supportEarning->buyer_tax = $buyerTax;
            $supportEarning->seller_fee = $sellerFeesAmount;
            $supportEarning->seller_tax = $sellerTax;
            $supportEarning->seller_earning = $sellerEarning;
            $supportEarning->support_expiry_at = $expiryDate;
            $supportEarning->save();

            // Update seller balance
            $seller->increment('balance', $supportEarning->seller_earning);

            // Create financial statements
            $this->createBuyerStatements($user, $trx, $product, $supportEarning);
            $this->createSellerStatements($seller, $product, $supportEarning);
        }

        // Update purchase support expiry date
        $this->updatePurchaseSupportExpiry($purchase, $expiryDate);
    }

    /**
     * Refund support earnings when purchase is refunded
     *
     * This reverses all financial transactions:
     * 1. Deduct earnings from seller balance
     * 2. Return payment to buyer balance
     * 3. Return taxes to buyer
     * 4. Create debit/credit statements
     * 5. Mark support earning as refunded
     *
     * @param Purchase $purchase The purchase being refunded
     * @return void
     */
    public function refund(Purchase $purchase): void
    {
        // Only refund if support is active (not expired)
        if (!$purchase->support_expiry_at || $purchase->isSupportExpired()) {
            return;
        }

        $supportEarning = $purchase->supportEarnings
            ->where('support_expiry_at', $purchase->support_expiry_at)
            ->first();

        if (!$supportEarning) {
            return;
        }

        $product = $purchase->product;
        $user = $purchase->user;
        $seller = $supportEarning->seller;

        // Reverse seller earnings
        $seller->decrement('balance', $supportEarning->seller_earning);
        $this->createSellerRefundStatements($seller, $product, $supportEarning);

        // Refund buyer payment
        $user->increment('balance', $supportEarning->price);
        $this->createBuyerRefundStatements($user, $product, $supportEarning);

        // Mark as refunded
        $supportEarning->status = SupportEarningStatus::REFUNDED;
        $supportEarning->save();
    }

    /**
     * Cancel support earnings when purchase is cancelled
     *
     * Similar to refund but for cancelled purchases.
     * Creates cancellation statements instead of refund statements.
     *
     * @param Purchase $purchase The purchase being cancelled
     * @return void
     */
    public function cancel(Purchase $purchase): void
    {
        // Only cancel if support is active (not expired)
        if (!$purchase->support_expiry_at || $purchase->isSupportExpired()) {
            return;
        }

        $supportEarning = $purchase->supportEarnings
            ->where('support_expiry_at', $purchase->support_expiry_at)
            ->first();

        if (!$supportEarning) {
            return;
        }

        $product = $purchase->product;
        $user = $purchase->user;
        $seller = $supportEarning->seller;

        // Reverse seller earnings
        $seller->decrement('balance', $supportEarning->seller_earning);
        $this->createSellerCancellationStatements($seller, $product, $supportEarning);

        // Refund buyer payment
        $user->increment('balance', $supportEarning->price);
        $this->createBuyerCancellationStatements($user, $product, $supportEarning);

        // Mark as cancelled
        $supportEarning->status = SupportEarningStatus::CANCELLED;
        $supportEarning->save();
    }

    /**
     * Calculate buyer tax based on transaction tax settings
     *
     * @param Transaction $trx Transaction with tax information
     * @param float $supportPrice Support package price
     * @return array|null Tax details (name, rate, amount) or null
     */
    private function calculateBuyerTax(Transaction $trx, float $supportPrice): ?array
    {
        if (!$trx->tax) {
            return null;
        }

        $buyerTaxAmount = ($supportPrice * $trx->tax->percentage) / 100;

        return [
            'name' => $trx->tax->name,
            'rate' => $trx->tax->percentage,
            'amount' => round($buyerTaxAmount, 2),
        ];
    }

    /**
     * Calculate seller tax based on country-specific tax rules
     *
     * @param User $seller The seller user
     * @param float $sellerEarningAmount Seller's earnings before tax
     * @param string|null $country Buyer's country code
     * @return array|null Tax details (name, rate, amount) or null
     */
    private function calculateSellerTax(User $seller, float $sellerEarningAmount, ?string $country): ?array
    {
        if (!$country) {
            return null;
        }

        $sellerTax = SellerTax::whereJsonContains('countries', $country)->first();

        if (!$sellerTax) {
            return null;
        }

        $sellerTaxAmount = ($sellerEarningAmount * $sellerTax->percentage) / 100;

        return [
            'name' => $sellerTax->name,
            'rate' => $sellerTax->percentage,
            'amount' => round($sellerTaxAmount, 2),
        ];
    }

    /**
     * Create buyer financial statements for new support purchase
     *
     * Creates:
     * - Debit statement for support payment
     * - Debit statement for buyer tax (if applicable)
     *
     * @param User $user Buyer user
     * @param Transaction $trx Transaction record
     * @param object $product Product purchased
     * @param SupportEarning $supportEarning Support earning record
     * @return void
     */
    private function createBuyerStatements(User $user, Transaction $trx, object $product, SupportEarning $supportEarning): void
    {
        // Determine transaction type (purchase or extend)
        $type = $trx->isTypeSupportExtend()
            ? translate('Support Extend')
            : translate('Support Purchase');

        // Support payment statement
        $statement = new Statement();
        $statement->user_id = $user->id;
        $statement->title = translate('[:type] #:id (:product_name)', [
            'type' => $type,
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->price;
        $statement->total = $supportEarning->price;
        $statement->type = StatementType::DEBIT;
        $statement->save();

        // Buyer tax statement (if applicable)
        if ($supportEarning->buyer_tax) {
            $taxStatement = new Statement();
            $taxStatement->user_id = $user->id;
            $taxStatement->title = translate('[:tax_name (:tax_rate%)] :type #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->buyer_tax['name'],
                'tax_rate' => $supportEarning->buyer_tax['rate'],
                'type' => $type,
            ]);
            $taxStatement->amount = $supportEarning->buyer_tax['amount'];
            $taxStatement->total = $supportEarning->buyer_tax['amount'];
            $taxStatement->type = StatementType::DEBIT;
            $taxStatement->save();
        }
    }

    /**
     * Create seller financial statements for new support earnings
     *
     * Creates:
     * - Credit statement for support earnings (after fees)
     * - Debit statement for seller tax (if applicable)
     *
     * @param User $seller Seller user
     * @param object $product Product sold
     * @param SupportEarning $supportEarning Support earning record
     * @return void
     */
    private function createSellerStatements(User $seller, object $product, SupportEarning $supportEarning): void
    {
        // Support earnings statement
        $statement = new Statement();
        $statement->user_id = $seller->id;
        $statement->title = translate('[Support Earnings] #:id (:product_name)', [
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->price;
        $statement->seller_fee = $supportEarning->seller_fee;
        $statement->total = ($supportEarning->price - $supportEarning->seller_fee);
        $statement->type = StatementType::CREDIT;
        $statement->save();

        // Seller tax statement (if applicable)
        if ($supportEarning->seller_tax) {
            $taxStatement = new Statement();
            $taxStatement->user_id = $seller->id;
            $taxStatement->title = translate('[:tax_name (:tax_rate%)] Support Earnings #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->seller_tax['name'],
                'tax_rate' => $supportEarning->seller_tax['rate'],
            ]);
            $taxStatement->amount = $supportEarning->seller_tax['amount'];
            $taxStatement->total = $supportEarning->seller_tax['amount'];
            $taxStatement->type = StatementType::DEBIT;
            $taxStatement->save();
        }
    }

    /**
     * Create seller refund statements (reverse earnings)
     *
     * @param User $seller Seller user
     * @param object $product Product
     * @param SupportEarning $supportEarning Support earning being refunded
     * @return void
     */
    private function createSellerRefundStatements(User $seller, object $product, SupportEarning $supportEarning): void
    {
        // Reverse earnings
        $statement = new Statement();
        $statement->user_id = $seller->id;
        $statement->title = translate('[Refund] Support Earnings #:id (:product_name)', [
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->seller_earning;
        $statement->seller_fee = $supportEarning->seller_fee;
        $statement->total = $supportEarning->price;
        $statement->type = StatementType::DEBIT;
        $statement->save();

        // Reverse seller tax
        if ($supportEarning->seller_tax) {
            $taxStatement = new Statement();
            $taxStatement->user_id = $seller->id;
            $taxStatement->title = translate('[Refund] :tax_name (:tax_rate%) Support Earnings #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->seller_tax['name'],
                'tax_rate' => $supportEarning->seller_tax['rate'],
            ]);
            $taxStatement->amount = $supportEarning->seller_tax['amount'];
            $taxStatement->total = $supportEarning->seller_tax['amount'];
            $taxStatement->type = StatementType::CREDIT;
            $taxStatement->save();
        }
    }

    /**
     * Create buyer refund statements (return payment)
     *
     * @param User $user Buyer user
     * @param object $product Product
     * @param SupportEarning $supportEarning Support earning being refunded
     * @return void
     */
    private function createBuyerRefundStatements(User $user, object $product, SupportEarning $supportEarning): void
    {
        // Return support payment
        $statement = new Statement();
        $statement->user_id = $user->id;
        $statement->title = translate('[Refund] Support #:id (:product_name)', [
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->price;
        $statement->total = $supportEarning->price;
        $statement->type = StatementType::CREDIT;
        $statement->save();

        // Return buyer tax
        if ($supportEarning->buyer_tax) {
            $user->increment('balance', $supportEarning->buyer_tax['amount']);

            $taxStatement = new Statement();
            $taxStatement->user_id = $user->id;
            $taxStatement->title = translate('[Refund] :tax_name (:tax_rate%) Support #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->buyer_tax['name'],
                'tax_rate' => $supportEarning->buyer_tax['rate'],
            ]);
            $taxStatement->amount = $supportEarning->buyer_tax['amount'];
            $taxStatement->total = $supportEarning->buyer_tax['amount'];
            $taxStatement->type = StatementType::CREDIT;
            $taxStatement->save();
        }
    }

    /**
     * Create seller cancellation statements (reverse earnings)
     *
     * @param User $seller Seller user
     * @param object $product Product
     * @param SupportEarning $supportEarning Support earning being cancelled
     * @return void
     */
    private function createSellerCancellationStatements(User $seller, object $product, SupportEarning $supportEarning): void
    {
        // Reverse earnings
        $statement = new Statement();
        $statement->user_id = $seller->id;
        $statement->title = translate('[Cancellation] Support Earnings #:id (:product_name)', [
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->seller_earning;
        $statement->seller_fee = $supportEarning->seller_fee;
        $statement->total = $supportEarning->price;
        $statement->type = StatementType::DEBIT;
        $statement->save();

        // Reverse seller tax
        if ($supportEarning->seller_tax) {
            $taxStatement = new Statement();
            $taxStatement->user_id = $seller->id;
            $taxStatement->title = translate('[Cancellation] :tax_name (:tax_rate%) Support Earnings #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->seller_tax['name'],
                'tax_rate' => $supportEarning->seller_tax['rate'],
            ]);
            $taxStatement->amount = $supportEarning->seller_tax['amount'];
            $taxStatement->total = $supportEarning->seller_tax['amount'];
            $taxStatement->type = StatementType::CREDIT;
            $taxStatement->save();
        }
    }

    /**
     * Create buyer cancellation statements (return payment)
     *
     * @param User $user Buyer user
     * @param object $product Product
     * @param SupportEarning $supportEarning Support earning being cancelled
     * @return void
     */
    private function createBuyerCancellationStatements(User $user, object $product, SupportEarning $supportEarning): void
    {
        // Return support payment
        $statement = new Statement();
        $statement->user_id = $user->id;
        $statement->title = translate('[Cancellation] Support #:id (:product_name)', [
            'id' => $supportEarning->id,
            'product_name' => $product->name,
        ]);
        $statement->amount = $supportEarning->price;
        $statement->total = $supportEarning->price;
        $statement->type = StatementType::CREDIT;
        $statement->save();

        // Return buyer tax
        if ($supportEarning->buyer_tax) {
            $user->increment('balance', $supportEarning->buyer_tax['amount']);

            $taxStatement = new Statement();
            $taxStatement->user_id = $user->id;
            $taxStatement->title = translate('[Cancellation] :tax_name (:tax_rate%) Support #:id (:product_name)', [
                'id' => $supportEarning->id,
                'product_name' => $product->name,
                'tax_name' => $supportEarning->buyer_tax['name'],
                'tax_rate' => $supportEarning->buyer_tax['rate'],
            ]);
            $taxStatement->amount = $supportEarning->buyer_tax['amount'];
            $taxStatement->total = $supportEarning->buyer_tax['amount'];
            $taxStatement->type = StatementType::CREDIT;
            $taxStatement->save();
        }
    }

    /**
     * Update purchase support expiry date
     *
     * If support already exists and isn't expired, extend it.
     * Otherwise, set new expiry date.
     *
     * @param Purchase $purchase Purchase to update
     * @param Carbon $expiryDate New expiry date
     * @return void
     */
    private function updatePurchaseSupportExpiry(Purchase $purchase, Carbon $expiryDate): void
    {
        if ($purchase->support_expiry_at) {
            // Only extend if current support hasn't expired
            if (!$purchase->isSupportExpired()) {
                $purchase->support_expiry_at = $purchase->support_expiry_at->addDays($expiryDate->diffInDays(Carbon::now()));
            } else {
                $purchase->support_expiry_at = $expiryDate;
            }
        } else {
            $purchase->support_expiry_at = $expiryDate;
        }

        $purchase->save();
    }
}
