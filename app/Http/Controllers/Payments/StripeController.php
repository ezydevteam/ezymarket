<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Enums\TransactionStatus;
use App\Events\TransactionPaid;
use App\Models\Financial\{Transaction, PaymentGateway};
use Stripe\Checkout\Session;
use Stripe\{Stripe, Customer, Webhook};
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;
use Illuminate\Http\{Request, Response, RedirectResponse};

class StripeController extends Controller
{
    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('stripe');

        if ($this->paymentGateway?->credentials?->secret_key) {
            Stripe::setApiKey($this->paymentGateway->credentials->secret_key);
        }
    }

    public function process($trx): string
    {
        try {
            $session = Session::create([
                'customer_creation' => 'always',
                'customer_email' => $trx->user->email,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'unit_amount' => round($this->paymentGateway->getChargeAmount($trx->total) * 100),
                        'currency' => $this->paymentGateway->getCurrency(),
                        'product_data' => [
                            'name' => @settings('general')->site_name,
                            'description' => translate('Payment for order #:number', ['number' => $trx->id]),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'cancel_url' => route('checkout.index', hash_encode($trx->id)),
                'success_url' => route('payments.ipn.stripe') . '?session_id={CHECKOUT_SESSION_ID}',
            ]);

            $trx->update(['payment_id' => $session->id]);

            return json_encode([
                'type' => 'success',
                'method' => 'redirect',
                'redirect_url' => $session->url,
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'type' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }
    }

    public function ipn(Request $request): RedirectResponse
    {
        $sessionId = $request->session_id;

        $trx = Transaction::where('user_id', authUser()->id)
            ->where('payment_id', $sessionId)
            ->whereIn('status', [TransactionStatus::PAID, TransactionStatus::UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid()) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid' || $session->status !== 'complete') {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            $customer = Customer::retrieve($session->customer);

            $updated = Transaction::where('id', $trx->id)
                ->where('status', TransactionStatus::UNPAID)
                ->update([
                    'payer_id' => $customer->id,
                    'payer_email' => $customer->email,
                    'status' => TransactionStatus::PAID,
                ]);

            if ($updated) {
                $trx->refresh();
                $trx->user->emptyCart();
                event(new TransactionPaid($trx));
            }

            return redirect($checkoutLink);
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
            return redirect($checkoutLink);
        }
    }

    public function webhook(Request $request): Response
    {
        $endpointSecret = $this->paymentGateway->credentials->webhook_secret;
        $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->getContent();

        if (!$payload) {
            return response('Invalid payload', 401);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            if ($event && $event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                $trx = Transaction::where('payment_id', $session->id)->unpaid()->first();

                if ($trx) {
                    $customer = Customer::retrieve($session->customer);

                    $updated = Transaction::where('id', $trx->id)
                        ->where('status', TransactionStatus::UNPAID)
                        ->update([
                            'payer_id' => $customer->id,
                            'payer_email' => $customer->email,
                            'status' => TransactionStatus::PAID,
                        ]);

                    if ($updated) {
                        $trx->refresh();
                        event(new TransactionPaid($trx));
                    }
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 401);
        } catch (UnexpectedValueException $e) {
            return response('Invalid payload', 401);
        }
    }
}

















