<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Events\TransactionPaid;
use App\Http\Controllers\Controller;
use App\Models\Financial\{Transaction, PaymentGateway};
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlutterwaveController extends Controller
{
    private readonly Client $client;
    private readonly string $baseUrl;

    public function __construct(
        private readonly ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('flutterwave');

        $this->baseUrl = 'https://api.flutterwave.com/v3';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . ($this->paymentGateway?->credentials->secret_key ?? ''),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function process($trx): string
    {
        $user = $trx->user;

        try {
            $reference = 'FLW-' . Str::random(10) . '-' . time();

            $body = [
                'tx_ref' => $reference,
                'customizations' => [
                    'title' => translate('Payment for order #:number', [
                        'number' => $trx->id,
                    ]),
                ],
                'amount' => amountFormat($this->paymentGateway->getChargeAmount($trx->total)),
                'currency' => $this->paymentGateway->getCurrency(),
                'payment_options' => 'card,banktransfer,ussd',
                'customer' => [
                    'name' => $user->full_name,
                    'email' => $user->email,
                ],
                'redirect_url' => route('payments.ipn.flutterwave'),
            ];

            $response = $this->client->post('/payments', [
                'json' => $body,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($result['status'] !== 'success') {
                throw new Exception(translate('An error occurred while calling the API'));
            }

            $trx->update(['payment_id' => $reference]);

            return json_encode([
                'type' => 'success',
                'method' => 'redirect',
                'redirect_url' => $result['data']['link'],
            ]);
        } catch (Exception $e) {
            return json_encode([
                'type' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }
    }

    public function ipn(Request $request)
    {
        try {
            $transactionId = $request->transaction_id;

            if (!$transactionId) {
                throw new Exception(translate('Transaction ID is required'));
            }

            $response = $this->client->get("/transactions/{$transactionId}/verify");
            $result = json_decode($response->getBody()->getContents(), true);

            if (!$result || $result['status'] !== 'success') {
                throw new Exception(translate('An error occurred while verifying the transaction'));
            }

            $data = $result['data'];

            $trx = Transaction::where('user_id', authUser()->id)
                ->where('payment_id', $data['tx_ref'])
                ->whereIn('status', [TransactionStatus::PAID, TransactionStatus::UNPAID])
                ->firstOrFail();

            $checkoutLink = route('checkout.index', hash_encode($trx->id));

            if ($trx->isPaid()) {
                $trx->user->emptyCart();
                return redirect($checkoutLink);
            }

            if ($data['status'] !== 'successful') {
                toastr()->error(translate('Payment failed'));
                return redirect($checkoutLink);
            }

            if ($trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update(['status' => TransactionStatus::PAID]);

                if ($updated) {
                    $trx->refresh();
                    $trx->user->emptyCart();
                    event(new TransactionPaid($trx));
                }
            }

            return redirect($checkoutLink);
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->route('transactions.index');
        }
    }

    public function webhook(Request $request)
    {
        try {
            $secretHash = $this->paymentGateway?->credentials->secret_hash ?? '';
            $signature = $request->header('verif-hash');

            if (!$signature || $signature !== $secretHash) {
                return response('Invalid signature', 401);
            }

            $payload = $request->all();

            if (!$payload || !isset($payload['data'])) {
                return response('Invalid payload', 401);
            }

            $data = $payload['data'];

            if (isset($payload['event']) && $payload['event'] === 'charge.completed' && $data['status'] === 'successful') {
                $trx = Transaction::where('payment_id', $data['tx_ref'])->unpaid()->first();

                if ($trx) {
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
        } catch (Exception $e) {
            return response($e->getMessage(), 500);
        }
    }
}

















