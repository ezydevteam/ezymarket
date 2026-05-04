<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Jobs\Admin\SendAdminProductUpdateEmail;
use App\Models\Admin;
use App\Facades\Notification;

class ProcessUpdatedProduct
{
    public function handle(ProductUpdated $event)
    {
        $productUpdate = $event->productUpdate;
        $product = $productUpdate->product;

        if ($product->isApproved()) {

            // Get all active admins who can manage products
            $admins = Admin::productAccess()->active()->get();

            foreach ($admins as $admin) {
                dispatch(new SendAdminProductUpdateEmail($admin, $productUpdate));
            }

            $title = translate('Product Update Request (:product_name)', ['product_name' => $product->name]);
            $image = $productUpdate->thumbnail_url ?? $product->thumbnail_url;
            $link = route('admin.products.updated.show', $productUpdate->id);
            Notification::sendAdminNotification($title, $image, $link);
        }
    }
}

















