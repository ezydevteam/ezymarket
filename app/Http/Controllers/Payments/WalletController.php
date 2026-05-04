<?php

namespace App\Http\Controllers\Payments;

use App\Enums\TransactionStatus;
use App\Models\Financial\Transaction;
use App\Events\TransactionPaid;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function process($trx): string
    {
        try {
            $user = $trx->user;

            if ($user->balance < $trx->total) {
                return json_encode([
                    'type' => 'error',
                    'msg' => translate('Your account wallet balance is insufficient'),
                ]);
            }

            if ($trx->status === TransactionStatus::UNPAID) {
                $updated = Transaction::where('id', $trx->id)
                    ->where('status', TransactionStatus::UNPAID)
                    ->update(['status' => TransactionStatus::PAID]);

                if ($updated) {
                    $user->decrement('balance', $trx->total);
                    $trx->refresh();
                    $user->emptyCart();
                    event(new TransactionPaid($trx));
                }
            }

            return json_encode([
                'type' => 'success',
                'msg' => translate('Payment completed successfully'),
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'type' => 'error',
                'msg' => $e->getMessage(),
            ]);
        }
    }
}


















