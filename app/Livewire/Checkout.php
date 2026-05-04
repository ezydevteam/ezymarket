<?php

namespace App\Livewire;

use App\Models\Financial\BuyerTax;
use App\Models\Financial\PaymentGateway;
use App\Traits\LivewireToastr;
use Livewire\Component;

class Checkout extends Component
{
    use LivewireToastr;

    public $user;
    public $trx;
    public $summary = [];
    public $payment_method;

    public $address_line_1;
    public $address_line_2;
    public $city;
    public $state;
    public $zip;
    public $country;

    public function mount()
    {
        $this->user = authUser();
        $this->payment_method = old('payment_method') ?? PaymentGateway::forTrx($this->trx)
            ->excludeBalanceIfZero(authUser()->balance)->active()->first()->alias;

        $user = $this->user;
        $this->address_line_1 = old('address_line_1') ?? ($user->address['line_1'] ?? null);
        $this->address_line_2 = old('address_line_2') ?? ($user->address['line_2'] ?? null);
        $this->city = old('city') ?? ($user->address['city'] ?? null);
        $this->state = old('state') ?? ($user->address['state'] ?? null);
        $this->zip = old('zip') ?? ($user->address['zip'] ?? null);
        $this->country = old('country') ?? ($user->address['country'] ?? null);
    }

    public function updateSummary()
    {
        $trx = $this->trx;
        
        $productTotal = 0;
        $supportTotal = 0;
        
        if ($trx->isTypePurchase()) {
            foreach ($trx->trxProducts as $trxProduct) {
                // Product total should only be price * quantity
                $productTotal += ($trxProduct->price * $trxProduct->quantity);
                
                if ($trxProduct->support) {
                    $supportTotal += $trxProduct->support->total;
                }
            }
        } else {
            $productTotal = $trx->amount;
        }
        
        $total = $trx->amount;

        $tax = null;
        if (!$trx->isTypeDeposit() && $this->country) {
            $buyerTax = BuyerTax::whereJsonContains('countries', $this->country)->first();

            if ($buyerTax) {
                $taxRate = $buyerTax->percentage;
                $taxAmount = ($total * $taxRate) / 100;

                $tax = [
                    'name' => $buyerTax->name,
                    'rate' => $taxRate,
                    'amount' => round($taxAmount, 2),
                ];

                $total = round($total + $taxAmount, 2);
            }
        }

        $gateway = null;
        if ($this->payment_method) {

            $paymentGateway = PaymentGateway::where('alias', $this->payment_method)
                ->active()->first();

            if ($paymentGateway && !$paymentGateway->isAccountBalance()) {
                $gatewayFees = $paymentGateway->fees;

                if ($gatewayFees > 0) {
                    $feesAmount = ($total * $gatewayFees) / 100;

                    $gateway = [
                        'name' => $paymentGateway->name,
                        'fees' => $gatewayFees,
                        'amount' => round($feesAmount, 2),
                    ];

                    $total = round($total + $feesAmount, 2);
                }
            }
        }

        $this->summary = [
            'product_total' => $productTotal,
            'support_total' => $supportTotal,
            'subtotal' => $trx->amount,
            'tax' => $tax,
            'gateway' => $gateway,
            'total' => $total,
        ];
    }

    public function render()
    {
        $this->updateSummary();

        $paymentGateways = PaymentGateway::forTrx($this->trx)
            ->excludeBalanceIfZero(authUser()->balance)->active()->get();

        return theme_view('livewire.checkout', [
            'paymentGateways' => $paymentGateways,
        ]);
    }
}
