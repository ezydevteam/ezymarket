<?php

namespace App\Http\Controllers\UserPanel;

use App\Enums\{BadgeAlias, IdDocumentType};
use App\Classes\{CountryList, Nationality, Localization};
use App\Events\IdVerificationPending;
use App\Facades\Notification;
use App\Http\Controllers\Controller;
use App\Models\{Badge, Settings, IdVerification, UserBadge, Financial\PayoutMethod};
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, JsonResponse, RedirectResponse};
use Illuminate\Support\{Str, Facades\Hash};
use Illuminate\Contracts\View\View;
use Exception;

class SettingsController extends Controller
{
    use HandlesValidation;

    function index(){
        //not set
    }

    public function account(): View
    {
        $user = authUser();
        return theme_view('userpanel.settings.account', compact('user'));
    }

    public function updateAccount(Request $request): JsonResponse
    {
        $user = authUser();
        $validator = $this->validateRequestJson($request, [
            'firstname' => ['nullable', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['nullable', 'string', 'block_patterns', 'max:50'],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{7,15}$/', 'block_patterns'],
            'address_line_1' => ['required', 'string', 'max:255', 'block_patterns'],
            'address_line_2' => ['nullable', 'string', 'max:255', 'block_patterns'],
            'city' => ['required', 'string', 'max:150', 'block_patterns'],
            'state' => ['required', 'string', 'max:150', 'block_patterns'],
            'zip' => ['required', 'string', 'max:100', 'block_patterns'],
            'country' => ['required', 'string', 'in:' . implode(',', array_keys(CountryList::all()))],
            'seller_type' => ['nullable', 'string', 'in:exclusive,non_exclusive'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $requiresVerification = $this->updateAccountData($user, $request->all());

            if ($requiresVerification) {
                return $this->successJson('A verification code has been sent to your new email.', ['redirect' => route('verification.email.otp')]);
            }

            return $this->successJson('Account details has been updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function profile(): View
    {
        $user = authUser();
        return theme_view('userpanel.settings.profile', compact('user'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = authUser();
        $rules = [
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'heading' => ['nullable', 'string', 'block_patterns', 'max:255'],
            'bio' => ['nullable', 'max:5000'],
            'social_links' => ['nullable', 'array'],
            'basic_info.birth_date' => ['nullable', 'date', 'before:today'],
            'basic_info.gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'basic_info.nationality' => ['nullable', 'string', 'in:' . implode(',', Nationality::codes())],
            'basic_info.timezone' => ['nullable', 'string', 'in:' . implode(',', array_keys(Settings::timezones()))],
            'basic_info.language' => ['nullable', 'string', 'in:' . implode(',', Localization::codes())],
            'basic_info.profession' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'basic_info.hobby' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'basic_info.company' => ['nullable', 'string', 'block_patterns', 'max:100'],
            'basic_info.business_email' => ['nullable', 'email', 'max:100', 'block_patterns'],
            'basic_info.website' => ['nullable', 'url', 'max:255', 'block_patterns'],
        ];

        // Ensure all social links are strings
        if ($request->has('social_links')) {
            foreach ($request->social_links as $key => $value) {
                $rules["social_links.{$key}"] = ['nullable', 'string', 'max:255', 'block_patterns'];
            }
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $this->updateProfileData($user, $request);
            return $this->successJson('Profile details has been updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function password(): View
    {
        return theme_view('userpanel.settings.password');
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = authUser();
        $isOtpVerified = session('password_reset_verified') === true;

        $rules = [
            'new-password' => ['required', 'string', 'min:8', 'confirmed', 'block_patterns', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).+$/'],
            'new-password_confirmation' => ['required', 'block_patterns'],
        ];

        if (!$isOtpVerified) {
            $rules['current-password'] = ['required', 'block_patterns'];
        }

        $validator = $this->validateRequestJson($request, $rules, [
            'new-password.regex' => translate('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'),
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $this->updateNewPassword($user, $request, $isOtpVerified);
            session()->forget('password_reset_verified');
            return $this->successJson('Account password has been changed successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function sendPasswordResetOtp(Request $request): JsonResponse
    {
        $user = authUser();
        $otp = $user->generateEmailOtp();

        Notification::sendPasswordResetNotification($user, $otp);

        session(['password_reset_email' => $user->email]);

        return $this->successJson('OTP code has been sent to your email address.', [
            'redirect' => route('user.settings.password.verify_otp')
        ]);
    }

    public function showPasswordResetOtpForm(): View
    {
        $user = authUser();
        $email = session('password_reset_email') ?: $user->email;

        return theme_view('auth.verify-otp', [
            'email' => $email,
            'title' => translate('Verify OTP'),
            'resendRoute' => route('user.settings.password.reset_otp'),
            'verifyRoute' => route('user.settings.password.verify_otp.submit')
        ]);
    }

    public function verifyPasswordResetOtp(Request $request): JsonResponse
    {
        $user = authUser();

        if ($user->verifyEmailOtp($request->otp)) {
            $user->clearEmailOtp();
            session(['password_reset_verified' => true]);

            return $this->successJson('Identity verified successfully. You can now change your password.', [
                'redirect' => route('user.settings.password')
            ]);
        }

        return $this->errorJson('The verification code you entered is invalid or expired.');
    }

    public function twoFactor(): View
    {
        $QR_Image = null;
        $user = authUser();

        if (!$user->google2fa_status) {
            $google2fa = app('pragmarx.google2fa');
            $secretKey = encrypt($google2fa->generateSecretKey());

            $user->update(['google2fa_secret' => $secretKey]);
            $QR_Image = $google2fa->getQRCodeInline(@settings('general')->site_name, $user->email, $user->google2fa_secret);
        }
        return theme_view('userpanel.settings.2fa', compact('user', 'QR_Image'));
    }

    public function enable2FA(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'otp_code' => ['required', 'numeric', 'digits:6'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();
        try {
            $this->update2FAEnableData($user, $request);
            return $this->successJson('2FA Authentication has been enabled successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function disable2FA(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'otp_code' => ['required', 'numeric', 'digits:6'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();
        try {
            $this->update2FADisableData($user, $request);
            return $this->successJson('2FA Authentication has been disabled successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function idVerification(): View
    {
        $user = authUser();
        $idVerification = $user->idVerifications()->latest()->first();

        return theme_view('userpanel.settings.id-verification', compact('user', 'idVerification'));
    }

    public function storeIdVerification(Request $request): JsonResponse
    {
        $rules = [
            'document_type' => ['required', 'string', 'in:' . IdDocumentType::NATIONAL_ID->value . ',' . IdDocumentType::PASSPORT->value],
        ];

        if ($request->document_type == IdDocumentType::NATIONAL_ID->value) {
            $rules['front_of_id'] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];
            $rules['back_of_id'] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];
            $rules['national_id_number'] = ['required', 'string', 'block_patterns', 'max:30'];
        } elseif ($request->document_type == IdDocumentType::PASSPORT->value) {
            $rules['passport'] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];
            $rules['passport_number'] = ['required', 'string', 'block_patterns', 'max:30'];
        }

        if (@settings('id_verification')->photo_verification) {
            $rules['selfie'] = ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'];
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();

        if ($user->isIdVerified() || $user->isIdPending()) {
            return $this->errorJson('You already have a verified or pending ID verification request.');
        }

        try {
            $this->submitIdVerificationData($user, $request);
            return $this->successJson('Your documents has been submitted successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function badges(): View
    {
        $user = authUser();
        $userBadges = UserBadge::where('user_id', $user->id)->with('badge')->get();

        return theme_view('userpanel.settings.badges', compact('user', 'userBadges'));
    }

    public function sortBadges(Request $request): JsonResponse
    {
        $user = authUser();
        if (!$request->has('ids') || is_null($request->ids)) {
            return $this->errorJson('Failed to sort the badges');
        }

        $ids = explode(',', $request->ids);
        foreach ($ids as $sortOrder => $id) {
            $userBadge = $user->badges->where('id', $id)->first();
            $userBadge->sort_id = ($sortOrder + 1);
            $userBadge->update();
        }
        return $this->successJson('Badges sorted successfully');
    }

    public function payout(): View
    {
        $user = authUser();
        $payoutMethods = PayoutMethod::active()->get();

        return theme_view('userpanel.settings.payout', compact('user', 'payoutMethods'));
    }

    public function updatePayout(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'payout_method' => ['nullable', 'integer', 'exists:payout_methods,id'],
            'payout_account' => ['nullable', 'string', 'max:500', 'block_patterns'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        if ($request->filled('payout_method') && !$request->filled('payout_account')) {
            return $this->errorJson('Payout account details are required when a method is selected.');
        }

        $user = authUser();
        $user->payout_method_id = $request->payout_method;
        $user->payout_account = $request->payout_account;

        if ($user->save()) {
            $payoutMethod = PayoutMethod::find($request->payout_method);
            if ($payoutMethod) {
                Notification::sendPayoutMethodUpdateNotification($user, $payoutMethod);
            }
            return $this->successJson('Payout settings updated successfully.');
        }

        return $this->errorJson('Failed to update payout settings.');
    }

    public function premium(): View
    {
        $user = authUser();
        return theme_view('userpanel.settings.premium', compact('user'));
    }

    public function cancelPremium(): JsonResponse
    {
        $user = authUser();
        $premium = $user->premium;

        if ($premium) {
            $premium->delete();
        }

        $badge = Badge::where('alias', BadgeAlias::PREMIUM_MEMBERSHIP)->first();
        if ($badge) {
            $user->removeBadge($badge);
        }

        return $this->successJson('Your premium membership has been cancelled');
    }

    public function notifications(): View
    {
        $user = authUser();

        $preferenceGroups = config('notifications.groups', []);

        // Filter available preference groups
        if (!isPremiumAvailable()) {
            unset($preferenceGroups['premium_features']);
        }

        if (!$user->isSeller()) {
            unset($preferenceGroups['earnings_payouts']);
        }

        $typeLabels = [
            'in_app' => translate('In-App'),
            'email' => translate('Email'),
            'push' => translate('Push')
        ];

        $userPreferences = $user->notification_preferences ?? [];

        return theme_view('userpanel.settings.notifications', compact('userPreferences', 'preferenceGroups', 'typeLabels'));
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $user = authUser();
        $preferences = $request->input('preferences', []);

        $preferenceGroups = config('notifications.groups', []);

        // Match the same logic to prevent bypassing
        if (!isPremiumAvailable()) {
            unset($preferenceGroups['premium_features']);
        }

        if (!$user->isSeller()) {
            unset($preferenceGroups['earnings_payouts']);
        }

        $userSpecific = $user->notification_preferences ?? [];

        foreach (['in_app', 'email', 'push'] as $type) {
            foreach ($preferenceGroups as $groupKey => $groupData) {
                $enabled = isset($preferences[$type][$groupKey]) &&
                    ($preferences[$type][$groupKey] == '1' ||
                        $preferences[$type][$groupKey] === true);

                $userSpecific[$type][$groupKey] = $enabled;
            }
        }

        $user->notification_preferences = $userSpecific;
        $user->update();

        return $this->successJson('Notification preferences updated successfully');
    }

    public function chatboxBlockedUsers(): View
    {
        $user = authUser();
        $blockedUsers = $user->getBlockedUsers();

        $formattedUsers = $blockedUsers->map(function ($blockedUser) use ($user) {
            return [
                'id' => $blockedUser->id,
                'name' => $blockedUser->full_name,
                'username' => $blockedUser->username,
                'avatar' => $blockedUser->avatar_url,
                'profile_link' => $blockedUser->profile_link,
                'blocked_at' => $user->chatbox_blocked_users[$blockedUser->id] ?? now()->toDateTimeString(),
            ];
        });

        // We use map to format it because the diffForHumans() format may be needed inside the blade.
        // And now $user->chatbox_blocked_users holds the exact block timestamps.
        $blockedUsersData = $formattedUsers->map(function($data){
            $data['blocked_at_human'] = \Carbon\Carbon::parse($data['blocked_at'])->diffForHumans();
            return $data;
        });

        return theme_view('userpanel.settings.chatbox', [
            'blockedUsers' => $blockedUsersData
        ]);
    }

    public function unblockUser(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user = authUser();
        $user->unblockUser($request->user_id);

        return $this->successJson('User unblocked successfully');
    }

    public function apiKey(): View
    {
        return theme_view('userpanel.settings.api-key', ['user' => authUser()]);
    }

    public function generateApiKey(): JsonResponse
    {
        $user = authUser();

        $apiKey = hash('sha256', hash_encode($user->id) . Str::random(16) . microtime());

        $user->api_key = $apiKey;
        $user->update();

        return $this->successJson('API key has been generated successfully');
    }

    /**
     * Handle account update logic
     */
    private function updateAccountData($user, array $data): bool
    {
        $oldEmail = $user->email;
        $emailChanged = ($oldEmail != $data['email']);
        $verify = (@settings('actions')->email_verification && $emailChanged) ? true : false;
        $country = $data['country'] ?? null;

        $address = [
            'line_1' => $data['address_line_1'],
            'line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $country,
        ];

        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->phone = $data['phone'] ?? null;
        $user->address = $address;
        $user->seller_type = $data['seller_type'] ?? null;
        $user->update();

        if ($emailChanged) {
            if ($verify) {
                $user->forceFill(['pending_email' => $data['email']])->save();
                $user->sendEmailChangeNotification($data['email']);
            } else {
                $user->email = $data['email'];
                $user->update();
            }
            Notification::sendEmailUpdateNotification($user);
        }

        $user->addCountryBadge($country);
        $user->addExclusiveSellerBadge();

        return $verify;
    }

    /**
     * Handle profile update logic
     */
    private function updateProfileData($user, Request $request): void
    {
        $profilesPath = 'images/profiles/' . strtolower(hash_encode($user->id)) . '/';

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            if (!checkImageSize($avatar, '140x140')) {
                throw new Exception('Avatar image must be 140x140px');
            }
            $avatar = imageUpload($avatar, $profilesPath, '140x140', null, $user->avatar);
        } else {
            $avatar = $user->avatar;
        }

        if ($request->hasFile('cover')) {
            $profileCover = $request->file('cover');
            if (!checkImageSize($profileCover, '1920x180')) {
                throw new Exception('Profile cover image must be 1920x180px');
            }
            $profileCover = imageUpload($profileCover, $profilesPath, '1920x180', null, $user->basic_info['cover'] ?? null);
        } else {
            $profileCover = $user->basic_info['cover'] ?? null;
        }

        $socialLinks = [];
        if ($request->has('social_links')) {
            foreach ($request->social_links as $key => $socialLink) {
                $socialLinks[$key] = $socialLink;
            }
        }

        $profileBio = $request->bio ? sanitizeRichText($request->bio) : null;

        // Build basic_info array
        $basicInfo = $user->basic_info ?? [];

        // Profile fields
        if ($profileCover) {
            $basicInfo['cover'] = $profileCover;
        }
        if ($request->heading) {
            $basicInfo['heading'] = $request->heading;
        }
        if ($profileBio) {
            $basicInfo['bio'] = $profileBio;
        }

        // Merge social links
        $basicInfo = array_merge($basicInfo, $socialLinks);

        // Merge additional basic_info fields from request
        if ($request->has('basic_info')) {
            foreach ($request->basic_info as $key => $value) {
                $basicInfo[$key] = $value;
            }
        }

        $user->avatar = $avatar;
        $user->basic_info = $basicInfo;
        $user->update();
    }

    /**
     * Handle the password update logic
     *
     * @param $user
     * @param $request
     * @param bool $skipCheck
     * @throws Exception
     */
    private function updateNewPassword($user, $request, bool $skipCheck = false): void
    {
        if (!$skipCheck) {
            if (!(Hash::check($request->get('current-password'), $user->password))) {
                throw new Exception(translate('Your current password does not matches with the password you provided'));
            }

            if (strcmp($request->get('current-password'), $request->get('new-password')) == 0) {
                throw new Exception(translate('New Password cannot be same as your current password. Please choose a different password'));
            }
        }

        $user->password = Hash::make($request->get('new-password'));
        $user->update();

        Notification::sendPasswordUpdateNotification($user);
    }

    private function update2FAEnableData($user, $request): void
    {
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp_code);
        if (!$valid) {
            throw new Exception('Invalid OTP code');
        }

        $user->update(['google2fa_status' => true]);
        session()->put('user_2fa', hash_encode($user->id));
    }

    private function update2FADisableData($user, $request): void
    {
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->otp_code);
        if (!$valid) {
            throw new Exception('Invalid OTP code');
        }

        $user->update(['google2fa_status' => false]);
        if ($request->session()->has('user_2fa')) {
            session()->forget('user_2fa');
        }
    }

    private function submitIdVerificationData($user, Request $request): void
    {
        $documents = ['front_of_id' => null, 'back_of_id' => null, 'passport' => null, 'selfie' => null];
        $hashId = strtolower(hash_encode($user->id));
        $driver = storageDriver();
        $disk = $driver ? $driver->alias : 'local';

        if ($request->document_type == IdDocumentType::NATIONAL_ID->value) {
            $documents['front_of_id'] = storageFileUpload($request->file('front_of_id'), "id-verification/docs/{$hashId}/", $disk);
            $documents['back_of_id'] = storageFileUpload($request->file('back_of_id'), "id-verification/docs/{$hashId}/", $disk);
            $documentNumber = $request->national_id_number;
        } else {
            $documents['passport'] = storageFileUpload($request->file('passport'), "id-verification/docs/{$hashId}/", $disk);
            $documentNumber = $request->passport_number;
        }

        if (@settings('id_verification')->photo_verification) {
            $documents['selfie'] = storageFileUpload($request->file('selfie'), "id-verification/docs/{$hashId}/", $disk);
        }

        $idVerification = new IdVerification();
        $idVerification->user_id = $user->id;
        $idVerification->document_type = $request->document_type;
        $idVerification->document_number = $documentNumber;
        $idVerification->documents = $documents;

        if (!$idVerification->save()) {
            throw new Exception('Failed to submit documents. Please try again.');
        }

        event(new IdVerificationPending($idVerification));
    }
}
