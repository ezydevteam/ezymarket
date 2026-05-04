<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!@settings('mail')->status) {
            die(translate('Mail server is not enabled, please enable the mail server from settings'));
        }
        return $next($request);
    }
}
