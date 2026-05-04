<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, Request, RedirectResponse};
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;

class VerificationController extends Controller
{
    use HandlesValidation;
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application.
    |
     */

    /**
     * Where to redirect users after verification.
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
        $this->middleware('auth');
        $this->middleware('throttle:6,1')->only('resend');
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show(Request $request): RedirectResponse
    {
        if (!@settings('actions')->email_verification) {
            return redirect()->route('home');
        }

        return $request->user()->hasVerifiedEmail()
            ? redirect($this->redirectPath())
            : redirect()->route('verification.otp');
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->ajax()
                ? $this->successJson('Email already verified.')
                : redirect($this->redirectPath());
        }

        // Standardize redirect to OTP resend route
        return redirect()->route('verification.otp.resend');
    }

    /**
     * Change email address.
     */
    public function changeEmail(Request $request): JsonResponse|RedirectResponse
    {
        $user = authUser();

        $rules = [
            'email' => ['required', 'string', 'email', 'indisposable', 'max:100', 'block_patterns', 'unique:users,email,' . $user->id],
        ];

        if ($request->ajax()) {
            $validator = $this->validateRequestJson($request, $rules);
            if ($validator instanceof JsonResponse) {
                return $validator;
            }
        } else {
            $request->validate($rules);
        }

        if ($user->email !== $request->email) {
            $user->forceFill(['pending_email' => $request->email])->save();

            $user->sendEmailChangeNotification($request->email);

            return $this->successRedirect('verification.email.otp', [], 'A verification code has been sent to your new email.');
        }

        return $request->ajax()
            ? $this->successJson('Email address is already set to this value.')
            : back();
    }

    /**
     * Show the OTP verification form for email change.
     */
    public function showEmailChangeOtpForm(): View|RedirectResponse
    {
        $user = authUser();
        if (!$user->pending_email) {
            return redirect()->route('user.settings.account');
        }

        return theme_view('auth.verify-otp', [
            'email' => $user->pending_email,
            'title' => translate('Verify New Email'),
            'resendRoute' => route('verification.email.otp.resend'),
            'verifyRoute' => route('verification.email.otp.verify')
        ]);
    }

    /**
     * Verify the OTP for email change.
     */
    public function verifyEmailChangeOtp(Request $request): JsonResponse|RedirectResponse
    {
        $rules = ['otp' => 'required|string|size:6'];

        if ($request->ajax()) {
            $validator = $this->validateRequestJson($request, $rules);
            if ($validator instanceof JsonResponse) {
                return $validator;
            }
        } else {
            $request->validate($rules);
        }

        $user = authUser();

        if ($user->verifyEmailOtp($request->otp)) {
            $user->forceFill([
                'email' => $user->pending_email,
                'pending_email' => null,
                'email_verified_at' => now(),
            ])->save();

            $user->clearEmailOtp();

            return $this->successRedirect('user.settings.account', [], 'Email updated and verified successfully!');
        }

        if ($request->ajax()) {
            return $this->errorJson('The provided code is invalid or has expired.', [
                'otp' => [translate('The provided code is expired or is invalid.')]
            ], 422);
        }

        toastr()->error(translate('The provided code is invalid or has expired.'));
        return back();
    }

    /**
     * Resend the OTP for email change.
     */
    public function resendEmailChangeOtp(Request $request): JsonResponse
    {
        $user = authUser();
        if (!$user->pending_email) {
            return $this->errorJson('No pending email change found.', [], 404);
        }

        // 1. Hard Cooldown (60s) to prevent burst spam
        if ($user->email_otp_expires_at && now()->diffInSeconds($user->email_otp_expires_at, false) > 240) {
            $remaining = now()->diffInSeconds($user->email_otp_expires_at, false) - 240;
            $time = $remaining < 60 ? $remaining . ' ' . translate('seconds') : ceil($remaining / 60) . ' ' . translate('minutes');

            return $this->errorJson('Please wait :time before requesting a new code.', [], 429, ['time' => $time]);
        }

        // 2. Volume Throttling (5 attempts per 30 mins)
        $key = 'resend-email-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $time = $seconds < 60 ? $seconds . ' ' . translate('seconds') : ceil($seconds / 60) . ' ' . translate('minutes');

            return $this->errorJson('Too many requests. Please try again in :time.', [], 429, ['time' => $time]);
        }

        RateLimiter::hit($key, 1800); // 30 minutes

        $user->sendEmailChangeNotification($user->pending_email);

        return $this->successJson('Verification code has been resent.');
    }
}


















