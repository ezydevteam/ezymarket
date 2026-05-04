<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin Product Pending Email
 *
 * Notifies administrators when a new product is submitted by a seller
 * and requires approval.
 *
 * Notification Details:
 * - Template: admin_product_pending
 * - Trigger: Seller submits a new product for review
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminProductPendingEmail extends SendEmailNotification
{
    /**
     * Create a new job instance
     *
     * @param mixed $admin Administrator to notify
     * @param mixed $product Product instance
     */
    public function __construct($admin, $product)
    {
        parent::__construct(
            notifiable: $admin,
            template: 'admin_product_pending',
            data: [
                'Seller_username' => $product->seller->full_name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_preview_image' => '<img src="' . $product->getImageLink() . '" width="100%"/>',
                'review_link' => route('admin.products.edit', $product->id),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.product.submitted'
        );
    }
}
