<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\GeolocationService;
use App\Traits\HandlesValidation;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Reefki\DeviceDetector\Device;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating admins for the application and
    | redirecting them to your dashboard screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect admins after login .
     *
     * @var string
     */
    public $redirectTo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    /**
     * Get the post-login redirect path based on admin role.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        // Get authenticated admin and redirect to role-specific landing page
        $admin = $this->guard()->user();

        if ($admin && $admin->role) {
            return $admin->role->landingPage();
        }

        // Fallback to default dashboard
        return route('admin.dashboard');
    }

    /**
     * Show the admin area login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Validate the admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ] + captchaRules();

        $request->validate($rules, $request->only(array_keys($rules)));
    }

    /**
     * Attempt to log the admin into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $username = $request->input($this->username());
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return $this->guard()->attempt(
            [
                $field => $username,
                'password' => $request->input('password'),
                'status' => true, // Only allow active accounts to login
            ],
            $request->filled('remember')
        );
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $admin
     * @return mixed
     */
    protected function authenticated(Request $request, Admin $admin)
    {
        // Log admin login activity
        if (method_exists($admin, 'logLoginActivity')) {
            $admin->logLoginActivity();
        }
    }

    /**
     * Get the failed login response with custom message for inactive accounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $username = $request->input($this->username());
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Track failed login attempt
        $this->trackFailedLoginAttempt($request, $username);

        // Check if account exists but is inactive
        $admin = Admin::where($field, $username)->first();

        if ($admin && method_exists($admin, 'isActive') && !$admin->isActive()) {
            // Check if password is correct
            if (Hash::check($request->input('password'), $admin->password)) {
                // Use HandlesValidation trait for consistent error display
                toastr()->error('Your account has been deactivated. Please contact the administrator.');

                return redirect()->back()
                    ->withInput($request->only($this->username(), 'remember'));
            }
        }

        // Default failed login response - show validation error
        toastr()->error(trans('auth.failed'));

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'));
    }

    /**
     * Log the admin out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $sessionKey = null;

        if ($this->guard()->check()) {
            $sessionKey = $this->guard()->user()->username;
        }

        $this->guard()->logout();

        if ($sessionKey) {
            $request->session()->forget($sessionKey);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->route('admin.login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email_or_username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }

    /**
     * Track failed login attempt
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $identifier
     * @return void
     */
    protected function trackFailedLoginAttempt(Request $request, ?string $identifier)
    {
        try {
            $ip = getIp();
            $ipLookup = app(GeolocationService::class)->lookup($ip);

            // Device detection using Device Facade
            $detector = Device::detectRequest($request);
            $client = $detector->getClient();
            $os = $detector->getOs();

            DB::table('failed_login_attempts')->insert([
                'guard' => 'admin',
                'identifier' => $identifier,
                'ip_address' => $ip,
                'location' => $ipLookup->location,
                'browser' => $client['name'] ?? 'Unknown',
                'browser_version' => $client['version'] ?? null,
                'os' => $os['name'] ?? 'Unknown',
                'device_type' => $detector->getDeviceName() ?: 'Desktop',
                'device_brand' => $detector->getBrandName() ?: null,
                'user_agent' => $request->userAgent(),
                'attempted_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break login flow if tracking fails
        }
    }
}
