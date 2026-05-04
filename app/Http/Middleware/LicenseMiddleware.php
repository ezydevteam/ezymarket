<?php

namespace App\Http\Middleware;

use Closure;

class LicenseMiddleware
{
    public function handle($request, Closure $next, $type)
    {
        abort_if(!get_license_type($type), 404);
        return $next($request);
    }
}


















