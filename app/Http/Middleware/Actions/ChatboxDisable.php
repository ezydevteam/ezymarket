<?php

namespace App\Http\Middleware\Actions;

use Closure;
use Illuminate\Http\Request;

class ChatboxDisable
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
        if (!@settings('chatbox')->status) {
            abort(503, translate('Chatbox is currently unavailable. We will be back soon.'));
        }

        return $next($request);
    }
}
