<?php

namespace Codebay\Installer\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotInstalledMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('system.install.complete')) {
            return redirect()->route('installer.index');
        }
        return $next($request);
    }
}


















