<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } else {
                switch ($guard) {
                    case 'admin':
                        return redirect()->guest(route('admin.login'));
                        break;
                    default:
                        return redirect()->guest(route('login'));
                }
            }
        }

        // Check if admin account is active
        if ($guard === 'admin' && Auth::guard($guard)->check()) {
            /** @var \App\Models\Admin|null $admin */
            $admin = Auth::guard($guard)->user();

            if (method_exists($admin, 'isActive') && !$admin->isActive()) {
                Auth::guard($guard)->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('error', translate('Your account has been deactivated. Please contact the administrator.'));
            }
        }

        return $next($request);
    }
}
