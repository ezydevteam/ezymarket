<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Sale;
use App\Models\Product\Product;

class CongratsNotification extends BaseNotification
{
    protected string $templateType;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $templateType, ?Product $product = null)
    {
        $this->user = $user;
        $this->templateType = $templateType;
        $this->product = $product;
    }

    public function getNotificationPreference(): string
    {
        return 'general';
    }

    public function toArray($notifiable)
    {
        $messages = [
            'top_seller' => translate('Congratulations! You are our Top Seller of the Month!'),
            'top_product' => translate("Your product :product_name is our Top Selling Product!", ['product_name' => $this->product?->name]),
        ];

        $data = [
            'type' => 'congratulations',
            'template' => $this->templateType,
            'title' => '🎉 ' . translate('Congratulations!'),
            'message' => $messages[$this->templateType] ?? translate('Congratulations on your achievement!'),
            'user_name' => $this->user?->full_name,
            'action_url' => route('user.dashboard'),
            'timestamp' => now()->toISOString(),
            'icon' => 'trophy',
            'color' => 'success'
        ];

        // Add product_id if available
        if ($this->product) {
            $data['product_id'] = $this->product->id;
            $data['product_name'] = $this->product->name;
            $data['product_url'] = $this->product->view_link;
        }

        return $data;
    }

    public function getEmailData()
    {
        // Map to your existing mail templates
        $templateMap = [
            'top_seller' => 'congratulate_top_seller',
            'top_product' => 'congratulate_top_product',
        ];

        // Calculate last month's sales statistics
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Query sales where this user is the seller
        $lastMonthSales = Sale::where('seller_id', $this->user->id)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->active()
            ->get();

        $totalSales = $lastMonthSales->count();
        $totalSalesAmount = $lastMonthSales->sum('price');

        $shortcodes = [
            'user_name' => $this->user?->full_name,
            'total_sales' => $totalSales,
            'total_sales_amount' => getAmount($totalSalesAmount),
            'website_name' => @settings('general')->site_name,
            'website_url' => @settings('general')->site_url,
        ];

        // Add product-related shortcodes only for top_product template
        if ($this->templateType === 'top_product' && $this->product) {
            // Calculate product sales statistics for last month
            $productSales = Sale::where('product_id', $this->product->id)
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->active()
                ->get();

            $productTotalSales = $productSales->count();
            $productTotalSalesAmount = $productSales->sum('price');
            $productTotalEarnings = $productSales->sum('seller_earning');

            $shortcodes['product_name'] = $this->product->name;
            $shortcodes['product_url'] = $this->product->view_link;
            $shortcodes['product_image'] = $this->product->thumbnail_url;
            $shortcodes['product_last_month_total_sales'] = $productTotalSales;
            $shortcodes['product_last_month_total_sales_amount'] = getAmount($productTotalSalesAmount);
            $shortcodes['product_last_month_total_earnings'] = getAmount($productTotalEarnings);
        }

        return [
            'template' => $templateMap[$this->templateType] ?? 'congratulate_top_seller',
            'shortcodes' => $shortcodes
        ];
    }
}
