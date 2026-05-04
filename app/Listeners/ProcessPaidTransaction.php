<?php

namespace App\Listeners;

use App\Enums\StatementType;
use App\Events\{SaleCreated, TransactionPaid};
use App\Facades\Notification;
use App\Http\Controllers\Theme\PremiumController;
use App\Jobs\Seller\DistributePremiumEarnings;
use App\Models\{Sale, User};
use App\Models\Financial\{SellerTax, Transaction, Statement};
use App\Services\SupportPaymentService;
use Illuminate\Support\Str;

/**
 * Process paid transactions and handle type-specific operations
 *
 * This listener handles:
 * - Purchase transactions (product sales)
 * - Support purchase transactions
 * - Support extend transactions
 * - Deposit transactions (balance top-up)
 * - Premium membership transactions
 *
 * Each transaction type triggers specific processing logic
 */
class ProcessPaidTransaction
{
    /**
     * Handle the transaction paid event
     */
    public function handle(TransactionPaid $event): void
    {
        $transaction = $event->transaction;
        $buyer = $transaction->user;

        try {
            if ($transaction->isPaid()) {
                // Send payment confirmation notification
                Notification::sendPaymentConfirmedNotification($buyer, $transaction);

                // Dynamically call the appropriate handler method based on transaction type
                $type = $transaction->type->value === 'premium' ? 'premium' : $transaction->type->value;
                $handlerMethod = 'handle' . Str::studly($type) . 'Transaction';
                $this->{$handlerMethod}($transaction);
            }
        } catch (\Exception $e) {
            // Show error message to user

        }
    }

    /**
     * Handle purchase transaction - process product sales
     */
    private function handlePurchaseTransaction(Transaction $transaction): void
    {
        // Check if this transaction has already been processed (idempotency)
        if (Sale::where('transaction_id', $transaction->id)->exists()) {
            return;
        }

        $transactionProducts = $transaction->trxProducts;
        $buyer = $transaction->user;
        $buyerCountry = $buyer->address['country'] ?? null;

        foreach ($transactionProducts as $transactionProduct) {
            $product = $transactionProduct->product;
            $seller = $product->seller;

            // Calculate fees and earnings
            $buyerFee = $transactionProduct->isRegularLicense()
                ? $product->category->regular_buyer_fee
                : $product->category->extended_buyer_fee;

            $priceAfterBuyerFee = $buyerFee > 0
                ? ($transactionProduct->price - $buyerFee)
                : $transactionProduct->price;

            $sellerFeePercentage = $seller->level->fees;
            $sellerFeeAmount = $sellerFeePercentage > 0
                ? ($priceAfterBuyerFee * $sellerFeePercentage) / 100
                : 0;

            $sellerEarning = $sellerFeeAmount > 0
                ? ($priceAfterBuyerFee - $sellerFeeAmount)
                : $priceAfterBuyerFee;

            // Calculate seller tax if applicable
            $sellerTaxData = null;
            $sellerTax = SellerTax::whereJsonContains('countries', $buyerCountry)->first();

            if ($sellerTax) {
                $sellerTaxAmount = ($sellerEarning * $sellerTax->percentage) / 100;
                $sellerEarning = ($sellerEarning - $sellerTaxAmount);

                $sellerTaxData = [
                    'name' => $sellerTax->name,
                    'rate' => $sellerTax->percentage,
                    'amount' => round($sellerTaxAmount, 2),
                ];
            }

            // Create sale records based on quantity
            for ($i = 0; $i < $transactionProduct->quantity; $i++) {
                $sale = $this->createSale(
                    $seller,
                    $buyer,
                    $product,
                    $transactionProduct,
                    $transaction,
                    $buyerFee,
                    $sellerFeeAmount,
                    $sellerTaxData,
                    $sellerEarning,
                    $buyerCountry,
                    $transaction->id
                );

                event(new SaleCreated($sale, $transaction, $transactionProduct->support));
            }
        }
    }

    /**
     * Create a sale record with all calculated fees and taxes
     */
    private function createSale(
        User $seller,
        User $buyer,
        $product,
        $transactionProduct,
        Transaction $transaction,
        float $buyerFee,
        float $sellerFeeAmount,
        ?array $sellerTaxData,
        float $sellerEarning,
        ?string $buyerCountry,
        int $transactionId
    ): Sale {
        $sale = new Sale();
        $sale->transaction_id = $transactionId;
        $sale->seller_id = $seller->id;
        $sale->user_id = $buyer->id;
        $sale->product_id = $product->id;
        $sale->license_type = $transactionProduct->license_type;
        $sale->price = $transactionProduct->price;
        $sale->buyer_fee = $buyerFee;

        // Add buyer tax if transaction has tax
        if ($transaction->hasTax()) {
            $buyerTaxAmount = ($transactionProduct->price * $transaction->tax->percentage) / 100;
            $sale->buyer_tax = [
                'name' => $transaction->tax->name,
                'rate' => $transaction->tax->percentage,
                'amount' => round($buyerTaxAmount, 2),
            ];
        }

        $sale->seller_fee = $sellerFeeAmount;
        $sale->seller_tax = $sellerTaxData;
        $sale->seller_earning = $sellerEarning;
        $sale->country = $buyerCountry;
        $sale->save();

        return $sale;
    }

    /**
     * Handle support purchase transaction
     */
    private function handleSupportPurchaseTransaction(Transaction $transaction): void
    {
        app(SupportPaymentService::class)->create(
            $transaction->purchase,
            $transaction,
            $transaction->support
        );
    }

    /**
     * Handle support extend transaction
     */
    private function handleSupportExtendTransaction(Transaction $transaction): void
    {
        app(SupportPaymentService::class)->create(
            $transaction->purchase,
            $transaction,
            $transaction->support
        );
    }

    /**
     * Handle deposit transaction - add funds to user balance
     */
    private function handleDepositTransaction(Transaction $transaction): void
    {
        $user = $transaction->user;

        // Check if a deposit statement already exists for this transaction (idempotency)
        // We use the title check because statements don't have a transaction_id column yet.
        $exists = Statement::where('user_id', $user->id)
            ->where('title', 'like', "%#{$transaction->id}%")
            ->where('type', StatementType::CREDIT)
            ->exists();

        if ($exists) {
            return;
        }

        // Increment user balance
        $user->increment('balance', $transaction->amount);

        // Create deposit statement
        Statement::create([
            'user_id' => $user->id,
            'title' => translate('[Deposit] Deposit to account balance #:id', [
                'id' => $transaction->id,
            ]),
            'amount' => $transaction->amount,
            'total' => $transaction->amount,
            'type' => StatementType::CREDIT,
        ]);
    }

    /**
     * Handle premium transaction - process premium purchase
     */
    private function handlePremiumTransaction(Transaction $transaction): void
    {
        $user = $transaction->user;
        $premiumPlan = $transaction->premiumPlan;

        // Create or update premium membership
        $premium = PremiumController::handlePremium($user, $premiumPlan);

        if (!$premium) {
            return;
        }

        // Create premium payment statement
        Statement::create([
            'user_id' => $user->id,
            'title' => translate('[Premium] #:id - :plan_name (:plan_interval)', [
                'id' => $premium->id,
                'plan_name' => $premiumPlan->name,
                'plan_interval' => $premiumPlan->interval_name,
            ]),
            'amount' => $transaction->amount,
            'total' => $transaction->amount,
            'type' => StatementType::DEBIT,
        ]);

        // Create tax statement if applicable
        if ($transaction->tax) {
            $premiumTax = $transaction->tax;

            Statement::create([
                'user_id' => $user->id,
                'title' => translate('[:tax_name (:tax_rate%)] Premium Membership #:id - :plan_name (:plan_interval)', [
                    'id' => $premium->id,
                    'plan_name' => $premiumPlan->name,
                    'plan_interval' => $premiumPlan->interval_name,
                    'tax_name' => $premiumTax->name,
                    'tax_rate' => $premiumTax->percentage,
                ]),
                'amount' => $premiumTax->amount,
                'total' => $premiumTax->amount,
                'type' => StatementType::DEBIT,
            ]);
        }

        // Dispatch job to distribute earnings
        dispatch(new DistributePremiumEarnings($transaction, $premium));
    }
}
