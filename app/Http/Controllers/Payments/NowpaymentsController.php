<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Events\TransactionPaid;
use App\Http\Controllers\Controller;
use App\Models\Financial\PaymentGateway;
use App\Models\Financial\Transaction;
use Illuminate\Http\Request;
use Codebay\NOWPayments\Authentication;
use Codebay\NOWPayments\Client;

class NowpaymentsController extends Controller
{
    private readonly Client $client;

    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('nowpayments');

        $this->client = new Client(
            $this->paymentGateway?->credentials->api_key ?? '',
            $this->paymentGateway?->isSandboxMode() ?? false
        );
    }

    public function process($trx)
    {
        try {
            $body = [
                'order_id' => $trx->id,
                'order_description' => translate('Payment for order #:number', [
                    'number' => $trx->id,
                ]),
                'price_amount' => $this->paymentGateway->getChargeAmount($trx->total),
                'price_currency' => $this->paymentGateway->getCurrency(),
                'ipn_callback_url' => route('payments.webhooks.nowpayments'),
                'success_url' => route('payments.ipn.nowpayments', ['id' => hash_encode($trx->id)]),
                'cancel_url' => route('checkout.index', hash_encode($trx->id)),
            ];

            /** @var \Codebay\NOWPayments\Client $client */
            $client = $this->client;
            $invoice = $client->invoice->create($body);

            $trx->payment_id = $invoice['id'];
            $trx->save();

            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $invoice['invoice_url'];
        } catch (\Exception $e) {
            $data['type'] = "error";
            $data['msg'] = $e->getMessage();
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $trx = Transaction::where('id', hash_decode($request->id))
            ->where('user_id', authUser()->id)
            ->whereIn('status', [TransactionStatus::PAID, TransactionStatus::UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid()) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            /** @var \Codebay\NOWPayments\Client $client */
            $client = $this->client;
            $payment = $client->payment->get($request->NP_id);

            if ($payment['payment_status'] == "finished") {
                if ($trx->status === TransactionStatus::UNPAID) {
                    $updated = Transaction::where('id', $trx->id)
                        ->where('status', TransactionStatus::UNPAID)
                        ->update(['status' => TransactionStatus::PAID, 'payment_id' => $payment['payment_id']]);

                    if ($updated) {
                        $trx->refresh();
                        $trx->user->emptyCart();
                        event(new TransactionPaid($trx));
                    }
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
        try {
            $authentication = Authentication::authenticate(
                $request->getContent(),
                $request->header('x-nowpayments-sig'),
                $this->paymentGateway->credentials->ipn_secret_key
            );

            if (!$authentication) {
                return response('Invalid signature', 401);
            }

            $payload = $request->all();

            if (!$payload) {
                return response('Invalid payload', 401);
            }

            if ($payload['payment_status'] == "finished") {
                $trx = Transaction::where('id', $payload['order_id'])
                    ->where('payment_id', $payload['invoice_id'])
                    ->unpaid()->first();

                if ($trx->status === TransactionStatus::UNPAID) {
                    $updated = Transaction::where('id', $trx->id)
                        ->where('status', TransactionStatus::UNPAID)
                        ->update(['status' => TransactionStatus::PAID, 'payment_id' => $payload['payment_id']]);

                    if ($updated) {
                        $trx->refresh();
                        event(new TransactionPaid($trx));
                    }
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}


















