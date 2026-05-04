<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Events\TransactionPaid;
use App\Http\Controllers\Controller;
use App\Models\Financial\{Transaction, PaymentGateway};
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayController extends Controller
{
    private readonly ?Api $api;

    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('razorpay');

        $this->api = ($this->paymentGateway?->credentials?->key_id && $this->paymentGateway?->credentials?->key_secret)
            ? new Api(
                $this->paymentGateway->credentials->key_id,
                $this->paymentGateway->credentials->key_secret
            )
            : null;
    }

    public function process($trx)
    {
        try {
            $razorpayAmount = round($this->paymentGateway->getChargeAmount($trx->total) * 100);

            $order = $this->api->order->create([
                'receipt' => "{$trx->id}",
                'amount' => $razorpayAmount,
                'currency' => $this->paymentGateway->getCurrency(),
            ]);

            $data['body'] = [
                'key' => $this->paymentGateway->credentials->key_id,
                'amount' => $razorpayAmount,
                'currency' => $this->paymentGateway->getCurrency(),
                'order_id' => $order['id'],
                'buttontext' => translate('Pay Now'),
                'name' => @settings('general')->site_name,
                'description' => translate('Payment for order #:number', [
                    'number' => $trx->id,
                ]),
                'image' => asset(themeSettings('general')->logo_dark),
                'prefill.name' => $trx->user->full_name,
                'prefill.email' => $trx->user->email,
                'theme.color' => themeSettings('colors')->primary_color,
            ];

            $trx->payment_id = $order['id'];
            $trx->update();

            $data['type'] = "success";
            $data['method'] = "hosted";
            $data['view'] = 'razorpay';
        } catch (\Exception $e) {
            $data['type'] = "error";
            $data['msg'] = $e->getMessage();
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $trxId = hash_decode($request->trx_id);
        $orderId = $request->razorpay_order_id;
        $paymentId = $request->razorpay_payment_id;

        $trx = Transaction::where('id', $trxId)
            ->where('user_id', authUser()->id)
            ->where(function ($query) use ($orderId, $paymentId) {
                $query->where('payment_id', $orderId)
                    ->orWhere('payment_id', $paymentId);
            })
            ->whereIn('status', [TransactionStatus::PAID, TransactionStatus::UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid()) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            $payment = $this->api->payment->fetch($paymentId);

            $signature = hash_hmac('sha256', $orderId . "|" . $paymentId, $this->paymentGateway->credentials->key_secret);
            if ($payment['status'] != 'captured' || $signature != $request->razorpay_signature) {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            if ($trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update(['status' => TransactionStatus::PAID, 'payment_id' => $payment['id']]);

                if ($updated) {
                    $trx->refresh();
                    $trx->user->emptyCart();
                    event(new TransactionPaid($trx));
                }
            }
            return redirect($checkoutLink);
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
            return redirect($checkoutLink);
        }
    }

    public function webhook(Request $request)
    {
        $webhookSecret = $this->paymentGateway->credentials->webhook_secret;

        $sigHeader = $request->header('x-razorpay-signature');
        $webhookBody = $request->getContent();

        try {
            $this->api->utility->verifyWebhookSignature($webhookBody, $sigHeader, $webhookSecret);

            $data = $request['payload'];

            if (!$data) {
                return response('Invalid payload', 401);
            }

            $trx = Transaction::where('payment_id', $data['payment']['entity']['order_id'])
                ->unpaid()->first();

            if ($trx && $trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update(['status' => TransactionStatus::PAID, 'payment_id' => $data['payment']['entity']['id']]);

                if ($updated) {
                    $trx->refresh();
                    event(new TransactionPaid($trx));
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (SignatureVerificationError $e) {
            return response('Invalid signature', 401);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}

















