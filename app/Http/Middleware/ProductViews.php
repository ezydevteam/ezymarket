<?php

namespace App\Http\Middleware;

use App\Models\Product\Product;
use App\Models\Product\ProductView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $productId = $request->route('id');
        $product = Product::where('id', $productId)->approved()->first();

        if ($product) {
            $ip = getIp();
            $referrer = $request->server('HTTP_REFERER');
            $referrerHost = parse_url($referrer, PHP_URL_HOST);
            $websiteUrl = parse_url(url('/'), PHP_URL_HOST);

            if ($referrerHost == $websiteUrl) {
                $referrer = '/';
            }

            $lastView = productView::where('product_id', $productId)
                ->where('ip', $ip)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$lastView || now()->diffInHours($lastView->created_at) >= 24) {
                $view = new ProductView();
                $view->product_id = $productId;
                $view->ip = $ip;
                $view->referrer = $referrer;
                $view->save();

                $product->increment('total_views');
                $product->increment('current_month_views');
            }
        }

        return $next($request);
    }
}


















