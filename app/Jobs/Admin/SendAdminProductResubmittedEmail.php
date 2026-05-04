<?php

namespace App\Jobs\Admin;

use App\Jobs\SendEmailNotification;

/**
 * Send Admin Product Resubmitted Email
 *
 * Notifies administrators when a seller resubmits a previously rejected
 * product for review.
 *
 * Notification Details:
 * - Template: admin_product_resubmitted
 * - Trigger: Seller resubmits a rejected product
 * - Recipient: Admin user
 *
 * @package App\Jobs\Admin
 */
class SendAdminProductResubmittedEmail extends SendEmailNotification
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
            template: 'admin_product_resubmitted',
            data: [
                'Seller_username' => $product->seller->full_name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_preview_image' => '<img src="' . $product->getImageLink() . '" width="100%"/>',
                'review_link' => route('admin.products.show', $product->id),
                'website_name' => @settings('general')->site_name,
            ],
            event: 'admin.product.resubmitted'
        );
    }
}
