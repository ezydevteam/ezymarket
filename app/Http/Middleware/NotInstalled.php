<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->is('install*') && !config('system.install.complete')) {
            return redirect()->route('installer.index');
        }

        if ($request->is('install*') && config('system.install.complete')) {
            return redirect('/');
        }

        return $next($request);
    }
}


















