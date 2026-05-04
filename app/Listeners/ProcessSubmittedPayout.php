<?php

namespace App\Listeners;

use App\Events\PayoutSubmitted;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminPayoutEmail;
use App\Models\Admin;

class ProcessSubmittedPayout
{
    public function handle(PayoutSubmitted $event)
    {
        $payout = $event->payout;

        $admins = Admin::financialAccess()->active()->get();

        foreach ($admins as $admin) {
            dispatch(new SendAdminPayoutEmail($admin, $payout));
        }

        $title = translate('New Payout Request [#:id]', ['id' => $payout->id]);
        $image = asset('images/notifications/withdrawal.png');
        $link = route('admin.financial.payouts.index', ['payout' => $payout->id]);
        Notification::sendAdminNotification($title, $image, $link);
    }
}
