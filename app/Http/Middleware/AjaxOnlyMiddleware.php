<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AjaxOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectRoute
     * @return mixed
     */
    public function handle($request, Closure $next, $redirectRoute = null)
    {
        if (!$request->ajax()) {
            if ($redirectRoute) {
                return redirect()->route($redirectRoute);
            }
            abort(404);
        }

        return $next($request);
    }
}


















