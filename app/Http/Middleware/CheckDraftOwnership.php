<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Product\Product;
use App\Enums\Product\ProductStatus;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDraftOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $draftId = $request->input('draft') ?? $request->input('draft_id');

        if ($draftId) {
            $isOwner = Product::where('id', $draftId)
                ->where('seller_id', authUser()->id)
                ->where('status', ProductStatus::DRAFT->value)
                ->exists();

            if (!$isOwner) {
                abort(403, translate('You do not have permission to access this draft or it does not exist.'));
            }
        }

        return $next($request);
    }
}
