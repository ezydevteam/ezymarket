<?php

namespace App\Http\Controllers\Auth;

use App\Events\Registered;
use App\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Models\{User, Settings};
use App\Traits\HandlesValidation;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\{Auth, Hash, RateLimiter};
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Exception;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers, HandlesValidation;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['showOtpForm', 'verifyOtp', 'resendOtp']);
    }

    /**
     * Show the application registration form.
     *
     * @param Request $request
     * @return View
     */
    public function showRegistrationForm(Request $request): View
    {
        return theme_view('auth.register');
    }

    /**
     * Before register a new user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $rules = $this->getRegisterRules();
        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = $this->create($validator->validated());
        event(new Registered($user));

        if (@settings('actions')->email_verification) {
            Auth::login($user);

            return $this->successJson('A verification code has been sent to your email address.', [
                'redirect' => route('verification.otp')
            ]);
        }

        $this->guard()->login($user);
        session()->put('registration_complete', true);

        $targetUrl = $this->getCustomRedirectUrl($user, 'register') ?? route('home');

        return $this->successJson('Congratulations! Registration successful', [
            'redirect' => $targetUrl
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data): User
    {
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_seller' => (bool)($data['is_seller'] ?? false),
        ]);

        $user->logLoginActivity();
        self::adminNotify($user);

        if (isset($data['newsletter']) && $data['newsletter']) {
            registerForNewsletter($user->email);
        }

        return $user;
    }

    /**
     * Check if a username is available via AJAX.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function checkUsernameAvailability(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'username' => [
                    'required',
                    'string',
                    'min:6',
                    'max:20',
                    'alpha_dash',
                    Rule::unique('users', 'username'),
                ],
            ]);

            return response()->json(['available' => true, 'message' => 'Username is available.'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'available' => false,
                'message' => $e->errors()['username'][0] ?? 'Username is unavailable.'
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['available' => false, 'message' => 'An error occurred while checking username.'], 500);
        }
    }

    /**
     * Show the OTP verification form for registration.
     *
     * @return View|RedirectResponse
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $user = authUser();
        if (!$user || $user->isEmailVerified()) {
            return redirect($this->redirectPath());
        }

        return theme_view('auth.verify-otp', [
            'email' => $user->email,
            'title' => translate('Verify your email'),
            'resendRoute' => route('verification.otp.resend'),
            'verifyRoute' => route('verification.otp.verify')
        ]);
    }

    /**
     * Verify the OTP for registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $rules = ['otp' => 'required|string|size:6'];
        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();

        if ($user->verifyEmailOtp($request->otp)) {
            $user->markEmailAsVerified();
            $user->clearEmailOtp();

            session()->put('registration_complete', true);

            $targetUrl = $this->getCustomRedirectUrl($user, 'register') ?? route('home');

            return $this->successJson('Email verified successfully!', [
                'redirect' => $targetUrl
            ]);
        }

        return $this->errorJson('The provided code is invalid or has expired.', [
            'otp' => [translate('The provided code is invalid or has expired.')]
        ], 422);
    }

    /**
     * Resend the OTP for registration.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $user = authUser();

        // 1. Hard Cooldown (60s) to prevent burst spam
        // Expires at T+300. If > 240 remains, then < 60s has passed.
        // We use absolute = false to ensure we don't block EXPIRED OTPs.
        if ($user->email_otp_expires_at && now()->diffInSeconds($user->email_otp_expires_at, false) > 240) {
            $remaining = now()->diffInSeconds($user->email_otp_expires_at, false) - 240;
            $time = $remaining < 60 ? $remaining . ' ' . translate('seconds') : ceil($remaining / 60) . ' ' . translate('minutes');

            return $this->errorJson('Please wait :time before requesting a new code.', [], 429, ['time' => $time]);
        }

        // 2. Volume Throttling (5 attempts per 30 mins)
        $key = 'resend-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $time = $seconds < 60 ? $seconds . ' ' . translate('seconds') : ceil($seconds / 60) . ' ' . translate('minutes');

            return $this->errorJson('Too many requests. Please try again in :time.', [], 429, ['time' => $time]);
        }

        RateLimiter::hit($key, 1800); // 30 minutes

        $user->sendEmailVerificationNotification();

        return $this->successJson(translate('Verification code has been resent.'));
    }

    /**
     * Get registration rules.
     *
     * @return array
     */
    protected function getRegisterRules(): array
    {
        $rules = [
            'username' => ['required', 'string', 'min:6', 'max:50', 'username', 'alpha_dash', 'block_patterns', 'unique:users'],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'is_seller' => ['nullable', 'boolean'],
            'newsletter' => ['nullable', 'boolean'],
        ] + captchaRules();

        if (@settings('links')->terms_of_use_link) {
            $rules['terms'] = ['required'];
        }

        return $rules;
    }

    /**
     * Get custom redirect URL fro header settings
     *
     * @param \App\Models\User $user
     * @param string $type
     * @return string|null
     */
    private function getCustomRedirectUrl(User $user, string $type = 'login'): ?string
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

    /**
     * Create a new admin notification
     *
     * @return void
     */
    public static function adminNotify($user): void
    {
        $title = translate(':username has registered', ['username' => $user->full_name]);
        $image = $user->avatar_url;
        $link = route('admin.roles.users.edit', $user->id);
        Notification::sendAdminNotification($title, $image, $link);
    }
}
