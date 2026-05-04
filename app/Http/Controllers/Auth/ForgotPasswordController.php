<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Hash, RateLimiter};
use Illuminate\Contracts\View\View;

class ForgotPasswordController extends Controller
{
    use HandlesValidation;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return View
     */
    public function showForgotForm(Request $request): View
    {
        return theme_view('auth.passwords.email');
    }

    /**
     * Send an OTP code to the given user.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $rules = ['email' => 'required|email'] + captchaRules();
        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $email = $request->input('email');

        // 1. Hard Rate Limiting (5 requests per 30 minutes)
        $key = 'password-reset:' . $request->ip() . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $time = $seconds < 60 ? $seconds . ' ' . translate('seconds') : ceil($seconds / 60) . ' ' . translate('minutes');

            return $this->errorJson('Too many requests. Please try again in :time.', [], 429, ['time' => $time]);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->errorJson('We couldn\'t find any user with this email.', [
                'email' => ['We couldn\'t find any user with this email.']
            ], 422);
        }

        RateLimiter::hit($key, 1800); // 30 minutes

        $user->sendPasswordResetNotification();

        session(['password_reset_email' => $user->email]);

        return $this->successJson('Verification code has been sent to your email.', [
            'redirect' => route('password.otp')
        ]);
    }

    /**
     * Show the OTP verification form for password reset.
     */
    public function showOtpForm(): View|RedirectResponse
    {
        $email = session('password_reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        return theme_view('auth.verify-otp', [
            'email' => $email,
            'title' => translate('Verify OTP'),
            'resendRoute' => route('password.otp.resend'),
            'verifyRoute' => route('password.otp.verify')
        ]);
    }

    /**
     * Verify the OTP for password reset.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $rules = ['otp' => 'required|string|size:6'];
        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $email = session('password_reset_email');
        $user = User::where('email', $email)->first();

        if ($user && $user->verifyEmailOtp($request->otp)) {
            session(['password_reset_verified' => true]);
            $user->clearEmailOtp();

            return $this->successJson('OTP verified successfully!', [
                'redirect' => route('password.reset')
            ]);
        }

        return $this->errorJson('The provided code is invalid or has expired.', [
            'otp' => [translate('The provided code is invalid or has expired.')]
        ], 422);
    }

    /**
     * Resend the OTP for password reset.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $email = session('password_reset_email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return $this->errorJson('User not found.', [], 404);
        }

        // 1. Hard Cooldown (60s) to prevent burst spam
        // We use absolute = false to ensure we don't block EXPIRED OTPs.
        if ($user->email_otp_expires_at && now()->diffInSeconds($user->email_otp_expires_at, false) > 240) {
            $remaining = now()->diffInSeconds($user->email_otp_expires_at, false) - 240;
            $time = $remaining < 60 ? $remaining . ' ' . translate('seconds') : ceil($remaining / 60) . ' ' . translate('minutes');

            return $this->errorJson('Please wait :time before requesting a new code.', [], 429, ['time' => $time]);
        }

        // 2. Volume Throttling (5 attempts per 30 mins)
        $key = 'password-reset-resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $time = $seconds < 60 ? $seconds . ' ' . translate('seconds') : ceil($seconds / 60) . ' ' . translate('minutes');

            return $this->errorJson('Too many requests. Please try again in :time.', [], 429, ['time' => $time]);
        }

        RateLimiter::hit($key, 1800); // 30 minutes

        $user->sendPasswordResetNotification();

        return $this->successJson('Verification code has been resent.');
    }

    /**
     * Show the new password form.
     */
    public function showNewPasswordForm(): View|RedirectResponse
    {
        if (!session('password_reset_verified')) {
            return redirect()->route('password.request');
        }

        return theme_view('auth.passwords.new-password');
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        if (!session('password_reset_verified')) {
            return $this->errorJson('Verification required.', [
                'redirect' => route('password.request')
            ], 403);
        }

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $email = session('password_reset_email');
        $user = User::where('email', $email)->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        session()->forget(['password_reset_email', 'password_reset_verified']);

        return $this->successJson('Your password has been reset successfully!', [
            'redirect' => route('login')
        ]);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request): void
    {
        $rules = ['email' => 'required|email'] + captchaRules();
        $request->validate($rules, $request->only(array_keys($rules)));
    }
}


















