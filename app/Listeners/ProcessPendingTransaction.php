<?php

namespace App\Listeners;

use App\Events\TransactionPending;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminTrxPendingEmail;
use App\Models\Admin;

class ProcessPendingTransaction
{
    public function handle(TransactionPending $event)
    {
        $transaction = $event->transaction;

        if ($transaction->isPending()) {
            $admins = Admin::financialAccess()->active()->get();

            foreach ($admins as $admin) {
                dispatch(new SendAdminTrxPendingEmail($admin, $transaction));
            }

            $title = translate('New Pending Transaction [#:id]', ['id' => $transaction->id]);
            $image = asset('images/notifications/transaction.png');
            $link = route('admin.financial.transactions.index', ['trx' => $transaction->id]);
            Notification::sendAdminNotification($title, $image, $link);
        }
    }
}


















