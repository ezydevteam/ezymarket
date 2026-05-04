<?php

declare(strict_types=1);

namespace App\Jobs\Seller;

use App\Enums\StatementType;
use App\Models\Product\Product;
use App\Models\Financial\Statement;
use App\Models\Premium\PremiumEarning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\DB;

/**
 * Distribute Premium Earnings Job
 *
 * Distributes premium earnings to sellers based on package percentage.
 * Creates earning records and updates seller balances with statements.
 *
 * @package App\Jobs\Seller
 */
class DistributePremiumEarnings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;
    public $premiumPlan;

    public function __construct($transaction, $premiumPlan)
    {
        $this->transaction = $transaction;
        $this->premiumPlan = $premiumPlan;
    }

    public function handle(): void
    {
        $transaction = $this->transaction;
        $premiumPlan = $transaction->premiumPlan;

        if (!$premiumPlan || $premiumPlan->seller_earning_percentage <= 0) {
            return;
        }

        $products = Product::whereNot('seller_id', $transaction->user->id)
            ->premium()
            ->approved()
            ->get();

        foreach ($products as $product) {
            $this->distributeToSeller($product, $premiumPlan);
        }
    }

    /**
     * Distribute earnings to a single seller
     *
     * @param Product $product
     * @param PremiumPlan $premiumPlan
     * @return void
     */
    private function distributeToSeller($product, $premiumPlan): void
    {
        $seller = $product->seller;
        $earningAmount = ($this->transaction->amount * $premiumPlan->seller_earning_percentage) / 100;

        if ($earningAmount < 0.01) {
            return;
        }

        DB::transaction(function () use ($seller, $product, $premiumPlan, $earningAmount) {
            // Create premium earning record
            $premiumEarning = PremiumEarning::create([
                'seller_id' => $seller->id,
                'premium_id' => $this->premiumPlan->id,
                'product_id' => $product->id,
                'name' => translate(':plan_name (:plan_interval)', [
                    'plan_name' => $premiumPlan->name,
                    'plan_interval' => $premiumPlan->interval_name,
                ]),
                'percentage' => $premiumPlan->seller_earning_percentage,
                'price' => $this->transaction->amount,
                'seller_earning' => round($earningAmount, 2),
            ]);

            // Update seller balance
            $seller->increment('balance', $premiumEarning->seller_earning);

            // Create statement record
            Statement::create([
                'user_id' => $seller->id,
                'title' => translate('[Premium Earning] #:id (:product_name)', [
                    'id' => $premiumEarning->id,
                    'product_name' => $product->name,
                ]),
                'amount' => $premiumEarning->seller_earning,
                'total' => $premiumEarning->seller_earning,
                'type' => StatementType::CREDIT,
            ]);
        });
    }
}
