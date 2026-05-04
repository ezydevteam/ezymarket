<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    use HandlesValidation;

    public function index(Request $request): View
    {
        $admin = authAdmin();
        if ($request->ajax()) {
            return view('admin.account.details', compact('admin'));
        }
        return view('admin.account.index', compact('admin'));
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $admin = authAdmin();
        $validator = $this->validateRequestWithoutInput($request, [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:5', 'alpha_dash', 'block_patterns', 'max:50', 'unique:admins,username,' . $admin->id],
            'email' => ['required', 'email', 'string', 'indisposable', 'block_patterns', 'max:100', 'unique:admins,email,' . $admin->id],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);
        if ($validator instanceof RedirectResponse) {
            return $validator;
        }
        $validated = $validator->validated();
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = imageUpload(
                $request->file('avatar'),
                'images/avatars/admins/',
                '120x120',
                null,
                $admin->avatar
            );
        }
        $admin->update([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'avatar' => $validated['avatar'] ?? $admin->avatar,
        ]);
        return $this->successBack('Account details updated successfully');
    }

    public function showPassword(Request $request): View
    {
        $admin = authAdmin();
        if ($request->ajax()) {
            return view('admin.account.partials.password', compact('admin'));
        }
        return view('admin.account.index', compact('admin'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = authAdmin();
        $validator = $this->validateRequestWithoutInput($request, [
            'current-password' => ['required', 'string'],
            'new-password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if ($validator instanceof RedirectResponse) {
            return $validator;
        }
        $currentPassword = $request->input('current-password');
        $newPassword = $request->input('new-password');
        if (!Hash::check($currentPassword, $admin->password)) {
            return $this->errorBack('Current password is incorrect');
        }
        if (Hash::check($newPassword, $admin->password)) {
            return $this->errorBack('New password must be different from current password');
        }
        $admin->updatePassword($newPassword);
        return $this->successBack('Password updated successfully');
    }

    public function show2FA(Request $request): View
    {
        $admin = authAdmin();
        $qrCode = null;
        if (!$admin->has2fa()) {
            $google2fa = app('pragmarx.google2fa');
            if (!$admin->google2fa_secret) {
                $secretKey = encrypt($google2fa->generateSecretKey());
                $admin->update(['google2fa_secret' => $secretKey]);
            }
            try {
                $qrCode = $google2fa->getQRCodeInline(
                    @settings('general')->site_name,
                    $admin->email,
                    $admin->google2fa_secret
                );
            } catch (\Exception $e) {
                $qrCode = null;
            }
        }
        if ($request->ajax()) {
            return view('admin.account.partials.security', compact('admin', 'qrCode'));
        }
        return view('admin.account.index', compact('admin', 'qrCode'));
    }

    public function enable2FA(Request $request): RedirectResponse
    {
        $admin = authAdmin();
        $validator = $this->validateRequestWithoutInput($request, [
            'otp_code' => ['required', 'numeric', 'digits:6'],
        ]);
        if ($validator instanceof RedirectResponse) {
            return $validator;
        }
        if (!$this->verifyOTP($admin, $request->input('otp_code'))) {
            return $this->errorBack('Invalid OTP code. Please try again');
        }
        $admin->enable2fa($admin->google2fa_secret);
        session()->put('admin_2fa', hash_encode($admin->id));
        return $this->successBack('2FA authentication enabled successfully');
    }

    public function disable2FA(Request $request): RedirectResponse
    {
        $admin = authAdmin();
        $validator = $this->validateRequestWithoutInput($request, [
            'otp_code' => ['required', 'numeric', 'digits:6'],
        ]);
        if ($validator instanceof RedirectResponse) {
            return $validator;
        }
        if (!$this->verifyOTP($admin, $request->input('otp_code'))) {
            return $this->errorBack('Invalid OTP code. Please try again');
        }
        $admin->disable2fa();
        session()->forget('admin_2fa');
        return $this->successBack('2FA authentication disabled successfully');
    }

    private function verifyOTP($admin, string $otpCode): bool
    {
        $google2fa = app('pragmarx.google2fa');
        return $google2fa->verifyKey($admin->google2fa_secret, $otpCode);
    }
}
