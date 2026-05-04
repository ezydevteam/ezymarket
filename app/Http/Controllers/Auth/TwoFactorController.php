<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorController extends Controller
{
    use HandlesValidation;

    public function show2FaVerifyForm(Request $request)
    {
        if (!authUser()->google2fa_status || (session()->has('user_2fa')
            && session('user_2fa') == hash_encode(authUser()->id))) {
            return redirect()->route('home');
        }

        return theme_view('auth.2fa');
    }

    public function verify2fa(Request $request): JsonResponse
    {
        $rules = [
            'otp_code' => ['required', 'numeric'],
        ];

        $validator = $this->validateRequestJson($request, $rules);
        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Rate Limiting
        $key = '2fa-verify:' . authUser()->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->errorJson('Too many attempts. Please try again in :seconds seconds.', [], 429, ['seconds' => $seconds]);
        }

        $google2fa = app('pragmarx.google2fa');
        /** @var User $user */
        $user = authUser();
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp_code);

        if (!$valid) {
            RateLimiter::hit($key, 60);
            return $this->errorJson('Invalid OTP code', ['otp_code' => [translate('Invalid OTP code')]], 422);
        }

        RateLimiter::clear($key);
        session()->put('user_2fa', hash_encode($user->id));

        return $this->successJson('2FA verification successful!', ['redirect' => route('home')]);
    }
}

















