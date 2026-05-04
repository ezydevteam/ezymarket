<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Admin\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Admin Role Middleware
 *
 * Verifies that the authenticated admin has one of the required roles.
 * Used for role-based access control on admin routes.
 */
class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role values to check (e.g., 'admin', 'manager' or 'admin,manager,reviewer')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')
                ->with('error', translate('Please login to access this area.'));
        }

        $admin = auth('admin')->user();

        // Check if admin account is active
        /** @var \App\Models\Admin|null $admin */
        if (!$admin->isActive()) {
            auth('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', translate('Your account has been deactivated. Please contact the administrator.'));
        }

        // If no roles specified, just check authentication (any authenticated admin can access)
        if (empty($roles)) {
            return $next($request);
        }

        // Handle comma-separated roles in a single parameter (e.g., 'admin,manager,reviewer')
        $roleList = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                // Split comma-separated roles
                $roleList = array_merge($roleList, array_map('trim', explode(',', $role)));
            } else {
                $roleList[] = $role;
            }
        }

        // Convert string roles to AdminRole enums
        $allowedRoles = [];
        foreach ($roleList as $role) {
            try {
                $allowedRoles[] = AdminRole::from($role);
            } catch (\ValueError $e) {
                // Invalid role provided in middleware
                abort(500, "Invalid role specified in middleware: {$role}");
            }
        }

        // Check if admin has one of the required roles
        if (!$admin->hasRole($allowedRoles)) {
            abort(403, translate('You do not have permission to access this resource.'));
        }

        return $next($request);
    }
}
