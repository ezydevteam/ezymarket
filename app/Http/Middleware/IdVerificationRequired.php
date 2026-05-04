<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdVerificationRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = authUser();

        if ($user && $user->requiresIdVerification()) {
            toastr()->info(translate('Please complete the ID verification'));
            return redirect()->route('user.settings.id-verification');
        }

        return $next($request);
    }
}


















