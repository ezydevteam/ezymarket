<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Events\TransactionPending;
use App\Http\Controllers\Controller;
use App\Models\Financial\{Transaction, PaymentGateway};
use Illuminate\Http\{Request, RedirectResponse};

class BankwireController extends Controller
{
    public function __construct(
        private ?PaymentGateway $paymentGateway = null
    ) {
        $this->paymentGateway ??= paymentGateway('bankwire');
    }

    public function process($trx): string
    {
        try {
            return json_encode([
                'type' => 'success',
                'method' => 'hosted',
                'view' => 'bankwire',
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'type' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }
    }

    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'checkout_id' => ['required', 'string'],
        ]);

        try {
            $trx = Transaction::where('user_id', authUser()->id)
                ->where('id', hash_decode($validated['checkout_id']))
                ->unpaid()
                ->firstOrFail();

            $paymentProof = storageFileUpload(
                $request->file('payment_proof'),
                'uploads/transactions/',
                'local',
                $trx->id
            );

            if (!$paymentProof) {
                toastr()->error(translate('Failed to upload payment proof'));
                return back();
            }

            $trx->update([
                'payment_proof' => $paymentProof,
                'status' => TransactionStatus::PENDING,
            ]);

            $trx->user->emptyCart();
            event(new TransactionPending($trx));

            toastr()->success(translate('Payment proof was sent successfully. Our team will review it as soon as possible'));
            return redirect()->route('user.transaction.show', $trx->id);
        } catch (\Exception $e) {
            toastr()->error($e->getMessage());
            return back();
        }
    }
}

















