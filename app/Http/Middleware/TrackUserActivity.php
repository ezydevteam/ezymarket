<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    /**
     * Updates last_active_at (timestamp) and is_online (boolean)
     * every time an authenticated user hits any web route.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->timestamps = false;          // don’t touch updated_at
            $user->last_active_at = now();
            $user->is_online = true;
            $user->saveQuietly();                // silent, no events
        }

        return $next($request);
    }
}

















