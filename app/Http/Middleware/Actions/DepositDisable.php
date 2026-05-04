<?php

namespace App\Http\Middleware\Actions;

use Closure;
use Illuminate\Http\Request;

class DepositDisable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!@settings('deposit')->status) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('Deposit is currently disabled. Please try again later.'),
                ], 403);
            }
            abort(404);
        }
        return $next($request);
    }
}


















