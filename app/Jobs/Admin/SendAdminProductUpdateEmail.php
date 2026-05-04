<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin Product Update Email
 *
 * Notifies administrators when a seller submits an update to an
 * existing approved product that requires review.
 *
 * Notification Details:
 * - Template: admin_product_update
 * - Trigger: Seller updates an approved product
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminProductUpdateEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $productUpdate Product update instance
     */
    public function __construct($admin, $productUpdate)
    {
        $product = $productUpdate->product;

        parent::__construct(
            notifiable: $admin,
            template: 'admin_product_update',
            data: [
                'Seller_username' => $product->seller->full_name,
                'product_id' => $product->id,
                'product_name' => $productUpdate->name,
                'product_preview_image' => '<img src="' . $productUpdate->preview_image_url . '" width="100%"/>',
                'review_link' => route('admin.products.updated.show', $productUpdate->id),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.product.updated'
        );
    }
}



















