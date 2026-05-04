<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Settings};
use App\Providers\RouteServiceProvider;
use App\Traits\HandlesValidation;
use App\Facades\Notification;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Session};
use Illuminate\Contracts\View\View;


class LoginController extends Controller
{
    use AuthenticatesUsers, HandlesValidation;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm(): View
    {
        return theme_view('auth.login');
    }

    public function username(): string
    {
        return 'email_or_username';
    }

    public function login(Request $request): JsonResponse
    {
        if ($request->has('intended_url')) {
            Session::put('url.intended', $request->input('intended_url'));
        }

        // Standardize validation (Strict JSON)
        $validator = $this->validateRequestJson($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ] + captchaRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Rate Limiting Throttling
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            return $this->errorJson('Too many login attempts. Please try again in :seconds seconds.', [], 429, ['seconds' => $seconds]);
        }

        // Attempt Login
        if ($this->attemptLogin($request)) {
            /** @var User $user */
            $user = $this->guard()->user();

            // Handle authenticated logic (e.g., suspension check)
            if ($response = $this->authenticated($request, $user)) {
                return $response;
            }

            $customRedirect = $this->getCustomRedirectUrl($user, 'login');
            $intendedRedirectUrl = $customRedirect ?? redirect()->intended(RouteServiceProvider::HOME)->getTargetUrl();

            return $this->successJson('Login successful!', ['redirect' => $intendedRedirectUrl]);
        }

        // Login Failed
        $this->incrementLoginAttempts($request);

        $username = $request->input($this->username());
        /** @var User|null $failedUser */
        $failedUser = User::where('email', $username)->orWhere('username', $username)->first();

        if ($failedUser) {
            Notification::sendLoginFailedNotification($failedUser);
        }

        return $this->errorJson('The credentials didn\'t match our records.', [
            $this->username() => ['The credentials didn\'t match our records.']
        ], 422);
    }

    protected function validateLogin(Request $request): void
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ] + captchaRules();

        $request->validate($rules, $request->only(array_keys($rules)));
    }

    protected function authenticated(Request $request, $user): ?JsonResponse
    {
        /** @var User $user */
        if ($user->isSuspended()) {
            Auth::logout();
            return $this->errorJson('Your account has been suspended', [], 403);
        }

        $user->logLoginActivity();

        Notification::sendLoginSuccessNotification($user);

        return null;
    }

    protected function attemptLogin(Request $request): bool
    {
        $username = $request->input($this->username());
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return $this->guard()->attempt(
            [
                $field => $username,
                'password' => $request->input('password'),
            ],
            $request->filled('remember')
        );
    }

    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->route('home');
    }

    /**
     * Get custom redirect URL fro header settings
     */
    private function getCustomRedirectUrl($user, string $type = 'login'): ?string
    {
        try {
            $settings = Settings::where('key', 'theme_header_layout')->value('value');
            if (!$settings) return null;

            // Normalize to array (handle string JSON or pre-casted Object/Array)
            if (is_string($settings)) {
                $layout = json_decode($settings, true);
            } else {
                $layout = json_decode(json_encode($settings), true);
            }

            if (!$layout || !is_array($layout)) return null;

            $authOptions = null;
            foreach ($layout as $section) {
                foreach ($section['columns'] ?? [] as $column) {
                    foreach ($column['blocks'] ?? [] as $block) {
                        if (in_array($block['id'] ?? '', ['header_auth', 'auth'])) {
                            $authOptions = $block['options'] ?? [];
                            break 3;
                        }
                    }
                }
            }

            if (!$authOptions) return null;

            $redirectType = $authOptions[$type . '_redirect'] ?? 'same_page';

            if ($redirectType === 'same_page') return null;

            return match ($redirectType) {
                'home' => route('home'),
                'profile' => route('profile.index', $user->username),
                'dashboard' => route('user.dashboard'),
                'custom' => $authOptions[$type . '_redirect_url'] ?? null,
                default => null
            };
        } catch (\Exception $e) {
            return null;
        }
    }
}
