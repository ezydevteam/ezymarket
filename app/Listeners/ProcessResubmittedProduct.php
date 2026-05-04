<?php

namespace App\Listeners;

use App\Events\ProductResubmitted;
use App\Facades\Notification;
use App\Jobs\Admin\SendAdminProductResubmittedEmail;
use App\Models\Admin;

class ProcessResubmittedProduct
{
    public function handle(ProductResubmitted $event)
    {
        $product = $event->product;

        if ($product->isResubmitted()) {
            $admins = Admin::productAccess()->active()->get();

            foreach ($admins as $admin) {
                dispatch(new SendAdminProductResubmittedEmail($admin, $product));
            }

            $title = translate('Product Resubmitted (:product_name)', ['product_name' => $product->name]);
            $image = $product->thumbnail_url;
            $link = route('admin.products.show', $product->id);
            Notification::sendAdminNotification($title, $image, $link);
        }
    }
}
