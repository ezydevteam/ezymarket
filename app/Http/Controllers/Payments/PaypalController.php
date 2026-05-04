<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Enums\TransactionStatus;
use App\Events\TransactionPaid;
use App\Models\Financial\{Transaction, PaymentGateway};
use Illuminate\Support\Facades\Validator;
use Codebay\PayPal\Core\{PayPalHttpClient, ProductionEnvironment, SandboxEnvironment};
use Codebay\PayPal\Orders\{OrdersCaptureRequest, OrdersCreateRequest};
use Illuminate\Http\Request;

class PaypalController extends Controller
{
    private readonly mixed $environment;

    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('paypal');

        $this->environment = match(true) {
            $this->paymentGateway?->isSandboxMode() => new SandboxEnvironment(
                $this->paymentGateway->credentials->client_id,
                $this->paymentGateway->credentials->client_secret
            ),
            $this->paymentGateway?->credentials !== null => new ProductionEnvironment(
                $this->paymentGateway->credentials->client_id,
                $this->paymentGateway->credentials->client_secret
            ),
            default => null
        };
    }

    public function process($trx)
    {
        if (!$this->environment) {
            throw new \Exception('PayPal payment gateway not configured');
        }

        $client = new PayPalHttpClient($this->environment);
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');

        $gateway = $this->paymentGateway;
        $currency = $gateway->getCurrency();

        $amount = amountFormat($gateway->getChargeAmount($trx->amount));
        $fees = amountFormat($gateway->getChargeAmount($trx->fees));
        $tax = $trx->tax ? amountFormat($gateway->getChargeAmount($trx->tax->amount)) : 0;
        $total = amountFormat($amount + $fees + $tax);

        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "reference_id" => $trx->id,
                "description" => translate('Payment for order #:number', [
                    'number' => $trx->id,
                ]),
                "amount" => [
                    "value" => $total,
                    "currency_code" => $currency,
                    "breakdown" => [
                        "product_total" => [
                            "value" => $amount,
                            "currency_code" => $currency,
                        ],
                        "handling" => [
                            "value" => $fees,
                            "currency_code" => $currency,
                        ],
                        "tax_total" => [
                            "value" => $tax,
                            "currency_code" => $currency,
                        ],
                    ],
                ],
            ]],
            "application_context" => [
                "return_url" => route('payments.ipn.paypal'),
                "cancel_url" => route('checkout.index', hash_encode($trx->id)),
                "shipping_preference" => "NO_SHIPPING",
            ],
        ];

        try {
            $response = $client->execute($request);

            $trx->payment_id = $response->result->id;
            $trx->update();

            $data['type'] = "success";
            $data['method'] = "redirect";
            $data['redirect_url'] = $response->result->links[1]->href;
        } catch (\Exception $e) {
            $data['type'] = "error";
            $data['msg'] = $e->getMessage();
        }

        return json_encode($data);
    }

    public function ipn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return redirect()->route('home');
        }

        $trx = Transaction::where('user_id', authUser()->id)
            ->where('payment_id', $request->token)
            ->whereIn('status', [TransactionStatus::PAID, TransactionStatus::UNPAID])
            ->firstOrFail();

        $checkoutLink = route('checkout.index', hash_encode($trx->id));

        if ($trx->isPaid()) {
            $trx->user->emptyCart();
            return redirect($checkoutLink);
        }

        try {
            $ordersCaptureRequest = new OrdersCaptureRequest($trx->payment_id);
            $ordersCaptureRequest->prefer('return=representation');
            $client = new PayPalHttpClient($this->environment);
            $response = $client->execute($ordersCaptureRequest);

            if (@$response->result->status != 'COMPLETED') {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            if ($trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update([
                        'status' => TransactionStatus::PAID,
                        'payer_id' => $response->result->payer->payer_id,
                        'payer_email' => $response->result->payer->email_address,
                    ]);

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
        $headers = $request->headers->all();
        $rawRequestBody = $request->getContent();

        try {
            $signatureVerified = $this->verifyPayPalSignature($headers, $rawRequestBody);
            if (!$signatureVerified) {
                return response('Invalid signature', 401);
            }

            $payload = json_decode($request->getContent(), true);

            if (!$payload) {
                return response('Invalid payload', 401);
            }

            if (isset($payload['event_type']) && $payload['event_type'] === 'PAYMENT.CAPTURE.COMPLETED') {
                if (isset($payload['resource']['status']) && $payload['resource']['status'] === 'COMPLETED') {
                    $supplementaryData = $payload['resource']['supplementary_data'];
                    $trx = Transaction::where('payment_id', $supplementaryData['related_ids']['order_id'])->unpaid()->first();
                        if ($trx->status === TransactionStatus::UNPAID) {
                            $updated = Transaction::where('id', $trx->id)
                                ->where('status', TransactionStatus::UNPAID)
                                ->update(['status' => TransactionStatus::PAID]);

                            if ($updated) {
                                $trx->refresh();
                                event(new TransactionPaid($trx));
                            }
                        }
                }
            }

            return response('Webhook processed successfully', 200);
        } catch (\Exception $e) {
            return response($e->getMessage(), 500);
        }
    }

    private function verifyPayPalSignature($headers, $rawRequestBody)
    {
        $transmissionId = $headers['paypal-transmission-id'][0];
        $timeStamp = $headers['paypal-transmission-time'][0];
        $crc32 = crc32($rawRequestBody);

        $webhookId = $this->paymentGateway->credentials->webhook_id;

        $inputString = implode('|', [
            $transmissionId,
            $timeStamp,
            $webhookId,
            $crc32,
        ]);

        $signature = $headers['paypal-transmission-sig'][0];

        $certUrl = $headers['paypal-cert-url'][0];

        $publicKey = openssl_pkey_get_public(file_get_contents($certUrl));

        $verified = openssl_verify(
            $inputString,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        return $verified === 1;
    }

}


















