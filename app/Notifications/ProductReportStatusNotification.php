<?php

namespace App\Notifications;

use App\Models\Product\Product;
use App\Models\Product\ProductReport;
use App\Models\User;

class ProductReportStatusNotification extends BaseNotification
{
    public $report;
    public $status;
    public $reason;
    public $product;
    public $user;

    public function __construct(Product $product, ProductReport $report, User $user, string $status)
    {
        $this->product = $product;
        $this->report = $report;
        $this->user = $user;
        $this->status = $status;
        $this->reason = $this->report->reason;
    }

    public function via($notifiable)
    {
        $channels = parent::via($notifiable);

        // Remove broadcast channel while keeping all others
        $key = array_search('broadcast', $channels);
        if ($key !== false) {
            unset($channels[$key]);
        }

        return array_values($channels);
    }

    protected function isProductSeller()
    {
        return (int) $this->product->seller->id === (int) $this->user->id;
    }

    protected function isproductReporter()
    {
        return (int) $this->report->user->id === (int) $this->user->id;
    }

    public function getNotificationPreference(): string
    {
        if ($this->status == 'deleted') {
            return 'reported_product_deletion';
        } elseif ($this->status == 'restricted') {
            return 'reported_product_restriction';
        } elseif ($this->status == 'un_restricted') {
            return 'reported_product_unrestriction';
        }

        return 'product_report_dismissed';
    }

    public function toArray($notifiable)
    {
        $data = [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'timestamp' => now()->toISOString(),
        ];

        switch ($this->status) {

            case 'deleted':
                if ($this->isProductSeller()) {
                    $data['type'] = 'reported_product_deletion';
                    $data['title'] = 'product Removed';
                    $data['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been removed due to several policy violation";
                    $data['action_url'] = route('user.product.index');
                    $data['icon'] = 'trash';
                    $data['color'] = 'error';
                } elseif ($this->isproductReporter()) {
                    $data['type'] = 'reported_product_deletion';
                    $data['title'] = 'Action Taken On Report';
                    $data['message'] = "We've removed '{$this->product->name}' [#{$this->product->id}] regarding to your report";
                    $data['action_url'] = $this->product->view_link;
                    $data['icon'] = 'trash';
                    $data['color'] = 'error';
                }
                break;

            case 'restricted':
                if ($this->isProductSeller()) {
                    $data['type'] = 'reported_product_restriction';
                    $data['title'] = 'product Restricted';
                    $data['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been restricted due to several policy violation";
                    $data['action_url'] = route('user.product.index');
                    $data['icon'] = 'exclamation-triangle';
                    $data['color'] = 'warning';
                } elseif ($this->isproductReporter()) {
                    $data['type'] = 'reported_product_restriction';
                    $data['title'] = 'Action Taken On Report';
                    $data['message'] = "We've restricted '{$this->product->name}' [#{$this->product->id}] regarding to your report";
                    $data['action_url'] = $this->product->view_link;
                    $data['icon'] = 'exclamation-triangle';
                    $data['color'] = 'warning';
                }
                break;

            case 'un_restricted':
                if ($this->isProductSeller()) {
                    $data['type'] = 'reported_product_unrestriction';
                    $data['title'] = 'product Un-restricted!';
                    $data['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been un-restricted and delivering to people";
                    $data['action_url'] = $this->product->view_link;
                    $data['icon'] = 'check2-circle';
                    $data['color'] = 'success';
                }
                break;

            case 'resolved':
            case 'cancelled':
                if ($this->isproductReporter()) {
                    $data['type'] = $this->status == 'resolved' ? 'product_report_resolved' : 'product_report_dismissed';
                    $data['title'] = 'Report ' . ucfirst($this->status);
                    $data['message'] = "Your report for '{$this->product->name}' [Report #{$this->report->id}] has been {$this->status}";
                    $data['action_url'] = $this->product->view_link;
                    $data['icon'] =  $this->status == 'resolved' ? 'check2-circle' : 'x-circle';
                    $data['color'] = $this->status == 'resolved' ? 'success' : 'error';
                }
                break;
        }

        return $data;
    }

    public function getEmailData()
    {
        $template = '';

        $shortCodes = [
            'username' => $this->isProductSeller() ? $this->product->SELLER->full_name : $this->report->user->full_name,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_preview_image' => '<img src="' . $this->product->getImageLink() . '" width="100%"/>',
            'report_id' => $this->report->id,
            'report_count' => $this->product->reportCounter(),
            'website_name' => @settings('general')->site_name,
        ];

        switch ($this->status) {

            case 'deleted':
                if ($this->isProductSeller()) {
                    $template = 'Seller_reported_product_deleted';
                    $shortCodes['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been removed due to several policy violation";
                    $shortCodes['action_url'] = route('user.product.index');
                } elseif ($this->isproductReporter()) {
                    $template = 'product_report_action';
                    $shortCodes['message'] = "We've removed '{$this->product->name}' [#{$this->product->id}] regarding to your report";
                }
                break;

            case 'restricted':
                if ($this->isProductSeller()) {
                    $template = 'Seller_reported_product_restricted';
                    $shortCodes['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been restricted due to several policy violation";
                    $shortCodes['action_url'] = route('user.product.index');
                    $shortCodes['restriction_reason'] = $this->report->reason;
                } elseif ($this->isproductReporter()) {
                    $template = 'product_report_action';
                    $shortCodes['message'] = "We've restricted '{$this->product->name}' [#{$this->product->id}] regarding to your report";
                    $shortCodes['action_url'] = $this->product->view_link;
                }
                break;

            case 'un_restricted':
                if ($this->isProductSeller()) {
                    $template = 'Seller_reported_product_unrestricted';
                    $shortCodes['message'] = "Your product '{$this->product->name}' [#{$this->product->id}] has been un-restricted and delivering to people";
                    $shortCodes['action_url'] = $this->product->view_link;
                }
                break;

            case 'resolved':
            case 'cancelled':
                if ($this->isproductReporter()) {
                    $template = 'product_report_action';
                    $shortCodes['status'] = ucfirst($this->status);
                    $shortCodes['action_url'] = $this->product->view_link;
                }
                break;
        }

        return [
            'template' => $template,
            'shortcodes' => $shortCodes
        ];
    }
}
