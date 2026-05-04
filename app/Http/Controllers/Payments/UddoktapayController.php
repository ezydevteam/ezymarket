<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Events\{TransactionPending, TransactionPaid};
use App\Http\Controllers\Controller;
use App\Models\Financial\{Transaction, PaymentGateway};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use UddoktaPay\LaravelSDK\UddoktaPay;

class UddoktapayController extends Controller
{
    private readonly ?UddoktaPay $uddoktaPay;

    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('uddoktapay');

        $this->uddoktaPay = ($this->paymentGateway?->credentials)
            ? new UddoktaPay(
                $this->paymentGateway->credentials->api_key ?? '',
                $this->paymentGateway->credentials->base_url ?? ''
            )
            : null;
    }

    public function process($trx)
    {
        $body = [
            'full_name' => $trx->user->full_name,
            'email' => $trx->user->email,
            'amount' => amountFormat($trx->total),
            'metadata' => [
                'trx_id' => $trx->id,
            ],
            'return_type' => 'GET',
            'redirect_url' => route('payments.ipn.uddoktapay', ['id' => hash_encode($trx->id)]),
            'cancel_url' => route('checkout.index', hash_encode($trx->id)),
            'webhook_url' => route('payments.webhooks.uddoktapay'),
        ];

        try {
            $paymentUrl = $this->uddoktaPay->initPayment($body);
            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $paymentUrl;
        } catch (\Exception $e) {
            $data['type'] = "error";
            $data['msg'] = $e->getMessage();
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
            'invoice_id' => ['required'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return redirect()->route('home');
        }

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
            $response = $this->uddoktaPay->verifyPayment($request->invoice_id);

             if ($response['status'] == 'ERROR') {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            if($response['status'] == 'PENDING'){

                $trx->status = TransactionStatus::PENDING;
                $trx->update();

                $trx->user->emptyCart();
                event(new TransactionPending($trx));

                toastr()->success(translate('Payment proof was sent successfully. Our team will review it as soon as possible'));
                return redirect()->route('user.transaction.show', $trx->id);

            }

            if ($trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update(['status' => TransactionStatus::PAID, 'payment_id' => $response['invoice_id']]);

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
        $httpApiKey = $request->header('HTTP_RT_UDDOKTAPAY_API_KEY');

        try {
            if (!isset($httpApiKey) || !$httpApiKey || empty($httpApiKey)) {
                return response('Http API Key not found', 401);
            }

            if ($httpApiKey != $this->paymentGateway->credentials->api_key) {
                return response('Invalid Http API Key', 401);
            }

            $payload = json_decode($request->getContent(), true);

            if (!$payload) {
                return response('Invalid payload', 401);
            }

            $payment = $this->uddoktaPay->verifyPayment($payload['invoice_id']);

            if ($payment['status'] == 'COMPLETED') {
                $trx = Transaction::where('id', $payment['metadata']['trx_id'])->unpaid()->first();
                if ($trx && $trx->status === TransactionStatus::UNPAID) {
                    $updated = Transaction::where('id', $trx->id)
                        ->where('status', TransactionStatus::UNPAID)
                        ->update([
                            'status' => TransactionStatus::PAID,
                            'payment_id' => $payment['invoice_id'],
                        ]);

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


















