<?php

namespace App\Http\Controllers\Auth;

use App\Events\Registered;
use App\Http\Controllers\Controller;
use App\Models\{SocialAuth, User};
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Hash, Validator};
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * Redirect the user to the social auth provider for authentication.
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        $socialAuth = SocialAuth::active()
            ->where('alias', $provider)
            ->firstOrFail();

        return Socialite::driver($socialAuth->alias)->redirect();
    }

    /**
     * Handles the callback from the social auth provider and creates or logs in the user.
     */
    public function handleProviderCallback(Request $request, string $provider): RedirectResponse
    {
        try {
            $socialAuth = SocialAuth::active()
                ->where('alias', $provider)
                ->firstOrFail();

            $socialUser = Socialite::driver($socialAuth->alias)->user();

            // Get user ID based on provider
            $providerId = $socialAuth->alias === 'envato'
                ? strtolower($socialUser->user['username'])
                : $socialUser->getId();

            // Check if user exists with this provider ID
            $user = $this->findOrCreateUser($socialAuth, $socialUser, $providerId);

            if ($user) {
                Auth::login($user);
                $user->logLoginActivity();
                return redirect($this->redirectTo);
            }

            toastr()->error(translate('Authentication failed. Please try again.'));
            return redirect()->route('login');

        } catch (\Exception $e) {
            toastr()->error(translate('Authentication failed. Please try again later.'));
            return redirect()->route('login');
        }
    }

    /**
     * Display the complete form for social authentication.
     */
    public function showCompleteForm(): View|RedirectResponse
    {
        if (authUser()->isDataCompleted()) {
            return redirect($this->redirectTo);
        }

        return theme_view('auth.data-complete');
    }

    /**
     * Complete the user profile update process.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = authUser();

        $rules = [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:6', 'max:50', 'username', 'alpha_dash', 'block_patterns', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:users,email,' . $user->id],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ] + captchaRules();

        if (@settings('links')->terms_of_use_link) {
            $rules['terms'] = ['required'];
        }

        $validator = Validator::make($request->only(array_keys($rules)), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                toastr()->error($error);
            }
            return back();
        }

        $needsVerification = @settings('actions')->email_verification && $user->email !== $request->email;

        $update = $user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($update) {
            if ($needsVerification) {
                $user->forceFill(['email_verified_at' => null])->save();
                $user->sendEmailVerificationNotification();
            }

            $user->addCountryBadge();
            RegisterController::adminNotify($user);
            return redirect($this->redirectTo);
        }

        toastr()->error(translate('Failed to update profile. Please try again.'));
        return back();
    }

    /**
     * Find existing user or create new one from social provider data.
     */
    private function findOrCreateUser(SocialAuth $socialAuth, $socialUser, string $providerId): ?User
    {
        // Check if user already exists with this provider ID (priority 1)
        $user = User::where($socialAuth->alias . '_id', $providerId)->first();

        if ($user) {
            return $user;
        }

        // Check if user exists with the same email (priority 2 - account linking)
        $email = $socialUser->getEmail();
        if ($email && $user = User::where('email', $email)->first()) {
            $user->update([$socialAuth->alias . '_id' => $providerId]);
            return $user;
        }

        // Collect data from provider
        $firstName = null;
        $lastName = null;
        $username = null;
        $raw = $socialUser->getRaw();

        switch ($socialAuth->alias) {
            case 'google':
                $firstName = $raw['given_name'] ?? null;
                $lastName = $raw['family_name'] ?? null;
                break;
            case 'facebook':
                $firstName = $raw['first_name'] ?? null;
                $lastName = $raw['last_name'] ?? null;
                break;
            case 'envato':
                $username = $raw['username'] ?? null;
                break;
            case 'github':
                $username = $socialUser->getNickname();
                break;
            case 'microsoft':
                $firstName = $raw['givenName'] ?? ($raw['given_name'] ?? null);
                $lastName = $raw['surname'] ?? ($raw['family_name'] ?? null);
                break;
        }

        // --- Opportunistic Data Collection ---
        $phone = null;
        $country = null;
        $dob = null;

        // Extract Avatar URL
        $avatarUrl = $socialUser->getAvatar();
        $avatarPath = null;
        if ($avatarUrl) {
            $avatarPath = $this->downloadSocialAvatar($avatarUrl);
        }

        switch ($socialAuth->alias) {
            case 'microsoft':
                $phone = $raw['mobilePhone'] ?? null;
                $dob = $raw['birthday'] ?? null;
                break;
            case 'facebook':
                $dob = $raw['birthday'] ?? null;
                if (isset($raw['location']['name'])) {
                    $country = $raw['location']['name'];
                }
                break;
        }

        // Fallback for names if not extracted
        if (!$firstName || !$lastName) {
            $parts = explode(' ', $socialUser->getName() ?? '', 2);
            $firstName = $firstName ?: ($parts[0] ?? null);
            $lastName = $lastName ?: ($parts[1] ?? null);
        }

        // Username logic (Nickname > Email Prefix)
        if (!$username) {
            $username = $socialUser->getNickname() ?: ($email ? explode('@', $email)[0] : null);
        }

        // Ensure unique username
        $username = $this->generateUniqueUsername($username ?: 'user');

        // Pre-fill address and basic_info arrays
        $address = $country ? ['country' => $country] : [];
        $basicInfo = $dob ? ['date_of_birth' => $dob] : [];

        // Create new user
        $user = User::create([
            'firstname' => $firstName,
            'lastname' => $lastName,
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'basic_info' => $basicInfo,
            'avatar' => $avatarPath,
            'password' => Hash::make(Str::random(16)),
            $socialAuth->alias . '_id' => $providerId,
        ]);

        if ($user) {
            // Verify email if provided
            if ($user->email) {
                $user->forceFill(['email_verified_at' => Carbon::now()])->save();
            }

            // Trigger registered event
            event(new Registered($user));

            // Register for newsletter
            registerForNewsletter($user->email);
        }

        return $user;
    }

    /**
     * Generate a unique username based on a seed string.
     */
    private function generateUniqueUsername(?string $seed): string
    {
        $username = Str::slug($seed ?: 'user', '');

        // Remove common domain parts or symbols if seed was an email
        $username = str_replace(['-', '.', '_'], '', $username);

        if (strlen($username) < 6) {
            $username = str_pad($username, 6, '0', STR_PAD_RIGHT);
        }

        $finalUsername = $username;
        $count = 1;

        while (User::where('username', $finalUsername)->exists()) {
            $suffix = $count === 1 ? rand(100, 999) : rand(1000, 9999);
            $finalUsername = substr($username, 0, 50 - strlen((string)$suffix)) . $suffix;
            $count++;
        }

        return $finalUsername;
    }

    /**
     * Download and save social avatar.
     */
    private function downloadSocialAvatar(string $url): ?string
    {
        try {
            $location = 'images/profiles/';
            makeDirectory(public_path($location));

            $extension = 'jpg'; // Default to jpg
            $filename = 'social_' . Str::random(10) . '_' . time() . '.' . $extension;
            $fullPath = public_path($location . $filename);

            // Fetch image content
            $content = file_get_contents($url);
            if (!$content) {
                return null;
            }

            // Save temporary
            $tempFile = tempnam(sys_get_temp_dir(), 'social_avatar');
            file_put_contents($tempFile, $content);

            // Process with Intervention Image
            $manager = \Intervention\Image\ImageManager::gd();
            $image = $manager->read($tempFile);

            // Resize to 140x140
            $image->cover(140, 140);

            // Encode and save
            file_put_contents($fullPath, $image->toJpeg());

            // Clean up
            @unlink($tempFile);

            return $location . $filename;
        } catch (\Exception $e) {
            return null;
        }
    }

}
