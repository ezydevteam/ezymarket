<?php

namespace App\Listeners;

use App\Events\ProductSubmitted;
use App\Jobs\Admin\SendAdminProductPendingEmail;
use App\Models\Admin;
use App\Facades\Notification;

class ProcessSubmittedProduct
{
    public function handle(ProductSubmitted $event)
    {
        $product = $event->product;

        if ($product->isPending()) {
            $admins = Admin::productAccess()->active()->get();

            foreach ($admins as $admin) {
                dispatch(new SendAdminProductPendingEmail($admin, $product));
            }

            $title = translate('New Pending Product (:product_name)', ['product_name' => $product->name]);
            $image = $product->thumbnail_url;
            $link = route('admin.products.show', $product->id);
            Notification::sendAdminNotification($title, $image, $link);
        }
    }
}
