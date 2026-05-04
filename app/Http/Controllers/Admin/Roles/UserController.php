<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Controller;
use App\Classes\CountryList;
use App\Enums\{BadgeAlias, StatementType, PremiumStatus};
use App\Enums\User\UserStatus;
use App\Models\{Referral, SellerLevel, User, Badge, UserBadge, Sale, Purchase, Support\Ticket};
use App\Models\Product\Product;
use App\Models\Financial\{PayoutMethod, Statement};
use App\Models\Premium\{PremiumPlan, Premium};
use App\Traits\{HandlesValidation, HandlesSorting};
use App\Cache\CacheManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * User Management Controller
 *
 * Handles all user-related administrative operations including CRUD,
 * account management, balance operations, and user settings.
 *
 * @package App\Http\Controllers\Admin\Roles
 */
class UserController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a paginated list of users with filters and statistics.
     *
     * Supports filtering by:
     * - Search term (firstname, lastname, username, email, etc.)
     * - Role (featured seller status)
     * - Account status (active/suspended)
     * - ID verification status
     * - Email verification status
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $counters = $this->getUserCounters();
        $query = User::query();

        // Handle DataTables AJAX requests
        if ($request->ajax() && $request->has('draw')) {
            try {
                $totalRecords = (clone $query)->count();

                // Apply filters, search and sorting
                $this->applyDataTableFilters($query);
                $filteredRecords = (clone $query)->count();
                $this->applyDataTableSorting($query);

                // Fetch Paginated Results
                $start = (int) $request->input('start', 0);
                $length = (int) $request->input('length', 10);
                $users = $query->skip($start)->take($length)->get();

                // Format Rows for DataTables
                $data = $users->map(fn($user) => $this->formatUserRow($user));

                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
            } catch (Exception $e) {
                return $this->errorJson($e->getMessage(), [], 500);
            }
        }

        $columns = $this->getDataTableColumns();
        $filters = $this->getDataTableFilters();
        $trashedCount = User::onlyTrashed()->count();
        $userCount = $query->count();

        return view('admin.roles.users.index', compact('counters', 'columns', 'filters', 'trashedCount', 'userCount'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return string
     */
    public function createModal(): string
    {
        return view('admin.roles.users.modals.modal_create')->render();
    }

    /**
     * Show the form for creating a new user.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.roles.users.create');
    }

    /**
     * Store a newly created user in the database.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $rules = [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:6', 'alpha_dash', 'username', 'block_patterns', 'max:50', 'unique:users'],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];

        // For AJAX requests, use JSON validation
        if ($request->ajax() || $request->wantsJson()) {
            $validator = $this->validateRequestJson($request, $rules);

            if ($validator instanceof JsonResponse) {
                return $validator;
            }
        } else {
            // For regular requests, use redirect validation
            $validator = $this->validateRequestWithInput($request, $rules);

            if ($validator instanceof RedirectResponse) {
                return $validator;
            }
        }

        $isSeller = false;
        $level = null;

        if ($request->has('seller') && $request->seller == 1) {
            $levelModel = SellerLevel::default()->with('badge')->first();
            if ($levelModel) {
                $isSeller = true;
                $level = $levelModel->id;
            }
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_seller' => $isSeller,
            'level_id' => $level,
        ]);

        if ($user) {
            if (@settings('actions')->email_verification) {
                $user->forceFill(['email_verified_at' => Carbon::now()])->save();
            }

            // If AJAX request, return JSON success
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'username' => $user->username,
                    ],
                ]);
            }

            return $this->createdRedirect('admin.roles.users.edit', $user->id);
        }

        // If AJAX request, return JSON error
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
            ], 500);
        }

        return back();
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param User $user
     * @return View
     */
    public function edit(Request $request, User $user): View
    {
        $data = $this->getTabData($user);
        $tab = $request->input('tab', 'overview');

        // Map tabs to their partial views
        $tabsMap = [
            'account'   => 'admin.roles.users.partials.account-content',
            'profile'   => 'admin.roles.users.partials.profile-content',
            'premium'   => 'admin.roles.users.partials.premium-content',
            'wallet'    => 'admin.roles.users.partials.balance-content',
            'security'  => 'admin.roles.users.partials.security-content',
            'badges'    => 'admin.roles.users.partials.badges-content',
            'referrals' => 'admin.roles.users.partials.referrals-content',
            'api-key'   => 'admin.roles.users.partials.api-key-content',
        ];

        // Determine which partial to show
        $activePartial = $tabsMap[$tab] ?? 'admin.roles.users.partials.overview';

        if ($request->ajax()) {
            return view($activePartial, $data);
        }

        // Add active tab and partial to the view data for initial load
        $data['activeTab'] = $tab;
        $data['activePartial'] = $activePartial;

        return view('admin.roles.users.edit', $data);
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:6', 'max:50', 'username', 'block_patterns', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'regex:/^\+?[0-9]{7,15}$/', 'block_patterns'],
            'address_line_1' => ['nullable', 'max:255'],
            'address_line_2' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:150'],
            'state' => ['nullable', 'max:150'],
            'zip' => ['nullable', 'max:100'],
            'country' => ['nullable', 'string', 'in:' . implode(',', array_keys(CountryList::all()))],
            'seller_type' => ['nullable', 'string', 'in:exclusive,non_exclusive'],
            'email_status' => ['nullable', 'boolean'],
            'is_id_verified' => ['nullable', 'boolean'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $address = [
                'line_1' => $request->address_line_1,
                'line_2' => $request->address_line_2,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
                'country' => $request->country ?? null,
            ];

            $updateData = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $address,
                'seller_type' => $request->seller_type,
            ];

            // Handle ID verification status
            if ($request->has('is_id_verified')) {
                $updateData['is_id_verified'] = (bool) $request->is_id_verified;
            }

            $user->update($updateData);

            // Handle email verification status using forceFill (as it's a protected field)
            if ($request->has('email_status')) {
                $emailVerifiedAt = $request->email_status ? Carbon::now() : null;
                $user->forceFill(['email_verified_at' => $emailVerifiedAt])->save();
            }

            return $this->successJson('User account has been updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Update user profile details.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateProfile(Request $request, User $user): JsonResponse
    {
        $rules = [
            'heading' => ['nullable', 'string', 'max:255', 'block_patterns'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
            'social_links' => ['nullable', 'array'],
            'basic_info' => ['nullable', 'array'],
            'basic_info.birth_date' => ['nullable', 'date', 'before:today'],
            'basic_info.gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'basic_info.nationality' => ['nullable', 'string', 'in:' . implode(',', \App\Classes\Nationality::codes())],
            'basic_info.timezone' => ['nullable', 'string', 'in:' . implode(',', array_keys(\App\Models\Settings::timezones()))],
            'basic_info.language' => ['nullable', 'string', 'in:' . implode(',', \App\Classes\Localization::codes())],
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
            $basicInfo = $user->basic_info ?? [];

            // Update profile content
            if ($request->has('heading')) $basicInfo['heading'] = $request->heading;
            if ($request->has('bio')) $basicInfo['bio'] = $request->bio;

            // Merge other basic info
            if ($request->has('basic_info')) {
                foreach ($request->basic_info as $key => $value) {
                    $basicInfo[$key] = $value;
                }
            }

            // Merge social links into basic_info
            if ($request->has('social_links')) {
                foreach ($request->social_links as $key => $value) {
                    $basicInfo[$key] = $value;
                }
            }

            $updateData = ['basic_info' => $basicInfo];

            // Handle Avatar Upload
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $fileName = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
                $path = 'uploads/users/avatars/' . $fileName;
                $avatar->move(public_path('uploads/users/avatars'), $fileName);

                // Delete old avatar if exists
                if ($user->avatar && file_exists(public_path($user->avatar))) {
                    @unlink(public_path($user->avatar));
                }

                $updateData['avatar'] = $path;
            }

            // Handle Cover Upload
            if ($request->hasFile('cover')) {
                $cover = $request->file('cover');
                $fileName = time() . '_' . uniqid() . '.' . $cover->getClientOriginalExtension();
                $path = 'uploads/users/covers/' . $fileName;
                $cover->move(public_path('uploads/users/covers'), $fileName);

                // Delete old cover if exists
                $oldCover = $user->basic_info['cover'] ?? null;
                if ($oldCover && file_exists(public_path($oldCover))) {
                    @unlink(public_path($oldCover));
                }

                $basicInfo['cover'] = $path;
                $updateData['basic_info'] = $basicInfo;
            }

            $user->update($updateData);

            return $this->successJson('User profile has been updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Update user's payout method and account.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updatePayout(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'payout_method' => ['nullable', 'integer', 'exists:payout_methods,id'],
            'payout_account' => ['nullable', 'string', 'max:500', 'block_patterns'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {

            if (!is_null($request->payout_method) && is_null($request->payout_account)) {
                return $this->errorJson('Payout account cannot be empty');
            }

            $payoutAccount = $request->payout_account;
            if (is_null($request->payout_method)) {
                $payoutAccount = null;
            }

            $user->update([
                'payout_method_id' => $request->payout_method,
                'payout_account' => $payoutAccount,
            ]);

            return $this->successJson('Payout details has been updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Update user status (suspend/activate).
     *
     * @param User $user
     * @return JsonResponse
     */
    public function updateStatus(User $user): JsonResponse
    {
        try {
            // Toggle status: if active -> suspend, if suspended -> activate
            $newStatus = $user->status === UserStatus::ACTIVE
                ? UserStatus::SUSPENDED
                : UserStatus::ACTIVE;

            // Update the status
            $user->update([
                'status' => $newStatus,
            ]);

            $message = $newStatus === UserStatus::ACTIVE
                ? translate('User has been activated successfully')
                : translate('User has been suspended successfully');

            return $this->successJson($message);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the user wallet management page.
     * Redirects to edit page with all tabs loaded.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function wallet(User $user): RedirectResponse
    {
        return $this->redirectToEdit($user, 'wallet');
    }

    /**
     * Update user wallet balance (credit or debit).
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateWallet(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'type' => ['required', 'string', 'in:credit,debit'],
            'amount' => ['required', 'regex:/^\d*(\.\d{2})?$/'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $type = null;
            $message = '';

            if ($request->type === 'credit') {
                $user->increment('balance', $request->amount);
                $type = StatementType::CREDIT;
                $message = 'User wallet has been credited successfully';
            } elseif ($request->type === 'debit') {
                $user->decrement('balance', $request->amount);
                $type = StatementType::DEBIT;
                $message = 'User wallet has been debited successfully';
            }

            if ($type) {
                Statement::create([
                    'user_id' => $user->id,
                    'title' => $request->note,
                    'amount' => $request->amount,
                    'total' => $request->amount,
                    'type' => $type,
                ]);
            }

            return $this->successJson($message);
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Assign a premium plan to a user
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function assignPremium(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:premium_plans,id'],
        ]);

        DB::beginTransaction();
        try {
            // Check if user already has a premium membership
            if ($user->premium) {
                return $this->errorJson('User already has an active premium membership. Please use upgrade instead.');
            }

            $premiumPlan = PremiumPlan::findOrFail($validated['plan_id']);
            $expiryAt = $premiumPlan->isLifetime()
                ? null
                : Carbon::now()->addDays($premiumPlan->interval_days);

            // Create new premium membership for the user
            Premium::create([
                'user_id' => $user->id,
                'plan_id' => $premiumPlan->id,
                'transaction_id' => 0, // Admin assigned, no transaction
                'status' => PremiumStatus::ACTIVE,
                'expiry_at' => $expiryAt,
            ]);

            DB::commit();
            return $this->successJson('Premium plan :premiumPlan has been assigned to user successfully', [], 200, ['premiumPlan' => $premiumPlan->name]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJson('Failed to assign premium plan: ' . $e->getMessage());
        }
    }

    /**
     * Upgrade a user's premium membership to a different premium plan
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function upgradePremium(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:premium_plans,id'],
        ]);

        DB::beginTransaction();
        try {
            $premium = $user->premium;

            if (!$premium) {
                return $this->errorJson('User does not have an active premium membership');
            }

            $newPremiumPlan = PremiumPlan::findOrFail($validated['plan_id']);

            if ($premium->plan_id === $newPremiumPlan->id) {
                return $this->errorJson('User is already subscribed to this premium plan');
            }

            $newExpiryAt = $newPremiumPlan->isLifetime()
                ? null
                : Carbon::now()->addDays($newPremiumPlan->interval_days);

            $premium->update([
                'plan_id' => $newPremiumPlan->id,
                'expiry_at' => $newExpiryAt,
                'created_at' => now(),
                'status' => PremiumStatus::ACTIVE,
            ]);

            DB::commit();
            return $this->successJson('Premium membership has been upgraded to :premiumPlan successfully', [], 200, ['premiumPlan' => $newPremiumPlan->name]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJson('Failed to upgrade premium membership: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a user's premium membership
     *
     * @param User $user
     * @return JsonResponse
     */
    public function cancelPremium(User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $subscription = $user->premium;

            if (!$subscription) {
                return $this->errorJson('User does not have an active premium membership');
            }

            // Delete the subscription
            $subscription->delete();

            DB::commit();
            return $this->successJson('Premium membership has been cancelled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJson('Failed to cancel premium membership: ' . $e->getMessage());
        }
    }

    /**
     * Show the password change form for the specified user.
     * Redirects to edit page with all tabs loaded.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function security(User $user): RedirectResponse
    {
        return $this->redirectToEdit($user, 'security');
    }

    /**
     * Update user password.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function updatePassword(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'new-password' => ['required', 'string', 'min:6', 'confirmed'],
            'new-password_confirmation' => ['required'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $user->update([
            'password' => bcrypt($request->get('new-password')),
        ]);

        return $this->successJson('Password has been updated successfully');
    }

    /**
     * Update user 2FA status.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function update2FA(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'google2fa_status' => ['required', 'boolean'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            if ($request->google2fa_status) {
                if (!$user->google2fa_status) {
                    return $this->errorJson('Two-Factor authentication cannot activated from admin side');
                }
                $user->update(['google2fa_status' => 1]);
            } else {
                $user->update(['google2fa_status' => 0]);
            }

            return $this->successJson('Two-Factor authentication has been updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to update Two-Factor authentication: ' . $e->getMessage());
        }
    }

    /**
     * Display user's referrals list with search functionality.
     * Redirects to edit page with all tabs loaded.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function referrals(User $user): RedirectResponse
    {
        return $this->redirectToEdit($user, 'referrals');
    }

    /**
     * Delete a specific referral.
     *
     * @param User $user
     * @param int $id
     * @return JsonResponse
     */
    public function deleteReferral(User $user, int $id): JsonResponse
    {
        try {
            $referral = Referral::where('id', $id)
                ->where('seller_id', $user->id)
                ->firstOrFail();

            $referral->delete();
            return $this->successJson('Referral has been deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to delete referral: ' . $e->getMessage());
        }
    }

    /**
     * Display user badges management page.
     * Redirects to edit page with all tabs loaded.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function badges(User $user): RedirectResponse
    {
        return $this->redirectToEdit($user, 'badges');
    }

    /**
     * Add a badge to the user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function addBadge(Request $request, User $user): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'badge' => ['required', 'integer', 'exists:badges,id'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $badge = Badge::findOrFail($request->badge);

            // Check if user already has this exact badge
            $existingBadge = UserBadge::where('user_id', $user->id)
                ->where('badge_id', $badge->id)
                ->first();

            if ($existingBadge) {
                return $this->errorJson('The user already has the selected badge');
            }

            // Check if user has a badge with the same alias (for upgrade)
            $existingBadgeWithSameAlias = UserBadge::where('user_id', $user->id)
                ->where('badge_alias', $badge->alias)
                ->first();

            // Add the badge
            $user->addBadge($badge);

            // Return appropriate message
            if ($existingBadgeWithSameAlias) {
                $message = (int)$existingBadgeWithSameAlias->id < (int)$badge->id
                    ? 'Badge has been upgraded successfully'
                    : 'Badge has been downgraded successfully';

                return $this->successJson($message);
            }
            return $this->successJson('Badge has been added successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Remove a badge from the user.
     *
     * @param Request $request
     * @param User $user
     * @param int $id
     * @return JsonResponse
     */
    public function deleteBadge(Request $request, User $user, int $id): JsonResponse
    {
        try {
            $userBadge = $user->badges()->where('id', $id)->firstOrFail();
            $userBadge->delete();

            return $this->successJson('Badge has been removed successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Display API key management page.
     * Redirects to edit page with all tabs loaded.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function apiKey(User $user): RedirectResponse
    {
        return $this->redirectToEdit($user, 'api-key');
    }

    /**
     * Generate a new API key for the user.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function apiKeyGenerate(Request $request, User $user): JsonResponse
    {
        try {
            $apiKey = hash('sha256', hash_encode($user->id) . Str::random(16) . microtime());

            $user->update(['api_key' => $apiKey]);

            return $this->successJson('API key has been generated successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Make a seller featured (only one featured seller at a time).
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function makeSellerFeatured(Request $request, User $user): JsonResponse
    {

        try {
            // Remove all other featured sellers
            User::featuredSeller()->update(['is_featured_seller' => false]);

            $user->update([
                'is_featured_seller' => true,
            ]);

            $badge = Badge::where('alias', BadgeAlias::FEATURED_SELLER)->first();
            if ($badge) {
                $user->addBadge($badge);
            }

            return $this->successJson('The seller is now featured');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to make seller featured');
        }
    }

    /**
     * Remove featured status from seller.
     *
     * @param Request $request
     * @param User $user
     * @return JsonResponse
     */
    public function removeSellerFeatured(Request $request, User $user): JsonResponse
    {
        try {
            $user->update([
                'is_featured_seller' => false,
            ]);

            return $this->successJson('Featured seller removed');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to remove featured seller');
        }
    }

    /**
     * Log in as the specified user (admin impersonation).
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function login(User $user): RedirectResponse
    {
        Auth::login($user);
        return redirect()->route('user.index');
    }

    /**
     * Remove the specified user.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $user->update(['deleted_by' => authAdmin()->id]);
            $user->delete();

            // Clear home page cache to reflect deleted user's products
            CacheManager::scope('home_')->flush();

            return $this->successJson('User deleted successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to delete user');
        }
    }

    /**
     * Display trashed (soft deleted) users.
     *
     * @return View
     */
    public function trash(): View
    {
        $users = User::onlyTrashed()->get();

        return view('admin.roles.users.trash', compact('users'));
    }

    /**
     * Restore a soft deleted user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            // Clear home page cache to reflect restored user's products
            CacheManager::scope('home_')->flush();

            return $this->successJson('User has been restored successfully');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to restore user: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function permanentlyDelete(int $id): JsonResponse
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->forceDelete();

            // Clear home page cache
            CacheManager::scope('home_')->flush();

            return $this->successJson('User has been permanently deleted.');
        } catch (\Exception $e) {
            return $this->errorJson('Failed to permanently delete user: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete users.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $users = User::whereIn('id', $ids)->get();

                foreach ($users as $user) {
                    $user->update(['deleted_by' => authAdmin()->id]);
                    $user->delete();
                }

                // Clear home page cache to reflect deleted users' products
                CacheManager::scope('home_')->flush();

                return count($users);
            },
            User::class,
            ':count user(s) deleted successfully',
            'Error deleting users'
        );
    }

    /**
     * Apply filter and search logic to the user query for DataTables.
     */
    private function applyDataTableFilters($query): void
    {
        if ($search = request()->input('search.value')) {
            $searchTerm = '%' . $search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('firstname', 'like', $searchTerm)
                    ->orWhere('lastname', 'like', $searchTerm)
                    ->orWhere('username', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm);
            });
        }

        if ($filters = request()->input('filters')) {
            foreach ($filters as $column => $value) {
                if ($value === null || $value === '') continue;

                // Column indexes based on getDataTableColumns()
                // bulk: 0, details: 1, premium: 2, seller: 3, status: 4, id_verified: 5, email_verified: 6, created_at: 7, actions: 8

                switch ($column) {
                    case '3': // Seller Role
                        $isSeller = ($value === translate('Yes')) ? 1 : 0;
                        $query->where('is_seller', $isSeller);
                        break;
                    case '4': // Account Status
                        $status = ($value === translate('Active')) ? UserStatus::ACTIVE->value : UserStatus::SUSPENDED->value;
                        $query->where('status', $status);
                        break;
                    case '5': // ID Verification
                        $isVerified = ($value === translate('Verified')) ? 1 : 0;
                        $query->where('is_id_verified', $isVerified);
                        break;
                    case '6': // Email Status
                        if ($value === translate('Verified')) {
                            $query->whereNotNull('email_verified_at');
                        } else {
                            $query->whereNull('email_verified_at');
                        }
                        break;
                    case '7': // Registered Date (Daterange)
                        if (is_array($value)) {
                            if (!empty($value['from']) && strtotime($value['from'])) {
                                $query->whereDate('created_at', '>=', $value['from']);
                            }
                            if (!empty($value['to']) && strtotime($value['to'])) {
                                $query->whereDate('created_at', '<=', $value['to']);
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * Apply sorting to the user query for DataTables.
     */
    private function applyDataTableSorting($query): void
    {
        $order = request()->input('order.0', []);
        $sortColumns = [
            1 => 'firstname',
            4 => 'status',
            7 => 'created_at'
        ];

        $columnIndex = $order['column'] ?? 7;
        $sortColumn = $sortColumns[$columnIndex] ?? 'id';
        $sortDir = $order['dir'] ?? 'desc';

        if ($sortColumn === 'firstname') {
            $query->orderBy('firstname', $sortDir)->orderBy('lastname', $sortDir);
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }
    }

    /**
     * Format a single user row for the DataTables AJAX response.
     */
    private function formatUserRow(User $user): array
    {
        $row = [
            'bulk' => '<input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="' . $user->id . '">',
            'details' => view('admin.roles.users.draw.details', compact('user'))->render(),
        ];

        if (isPremiumAvailable()) {
            $row['premium'] = view('admin.roles.users.draw.premium', compact('user'))->render();
        }

        $row['seller'] = view('admin.roles.users.draw.seller', compact('user'))->render();
        $row['status'] = view('admin.roles.users.draw.status', compact('user'))->render();

        if (@settings('actions')->id_verification) {
            $row['id_verified'] = view('admin.roles.users.draw.id_verification', compact('user'))->render();
        }

        $row['email_verified'] = view('admin.roles.users.draw.email_status', compact('user'))->render();
        $row['created_at'] = '<span class="text-muted">' . dateFormat($user->created_at) . '</span>';
        $row['actions'] = view('admin.roles.users.draw.actions', compact('user'))->render();

        return $row;
    }

    /**
     * Get columns configuration for the Datatable.
     */
    private function getDataTableColumns(): array
    {
        $columns = [
            ['data' => 'bulk', 'title' => '<input type="checkbox" class="form-check-input bulk-select-checkbox">', 'orderable' => false, 'searchable' => false, 'exportable' => false],
            ['data' => 'details', 'name' => 'firstname', 'title' => translate('User Details'), 'orderable' => true, 'searchable' => true],
        ];

        if (isPremiumAvailable()) {
            $columns[] = ['data' => 'premium', 'name' => 'premium', 'title' => translate('Premium Member'), 'orderable' => false, 'searchable' => false, 'centered' => true];
        }

        $columns[] = ['data' => 'seller', 'name' => 'is_seller', 'title' => translate('Seller'), 'orderable' => false, 'searchable' => false, 'centered' => true];
        $columns[] = ['data' => 'status', 'name' => 'status', 'title' => translate('Account status'), 'orderable' => true, 'searchable' => false, 'centered' => true];

        if (@settings('actions')->id_verification) {
            $columns[] = ['data' => 'id_verified', 'name' => 'is_id_verified', 'title' => translate('ID Verification'), 'orderable' => false, 'searchable' => false, 'centered' => true];
        }

        $columns[] = ['data' => 'email_verified', 'name' => 'email_verified_at', 'title' => translate('Email status'), 'orderable' => false, 'searchable' => false, 'centered' => true];
        $columns[] = ['data' => 'created_at', 'name' => 'created_at', 'title' => translate('Registered Date'), 'orderable' => true, 'searchable' => false, 'centered' => true];
        $columns[] = ['data' => 'actions', 'title' => translate('Actions'), 'orderable' => false, 'searchable' => false, 'exportable' => false, 'class' => 'text-end'];

        return $columns;
    }

    /**
     * Get filters configuration for the Datatable.
     */
    private function getDataTableFilters(): array
    {
        $filters = [];
        $columnIndex = 2; // Starting after details

        if (isPremiumAvailable()) {
            $columnIndex++; // Skip premium col for filter mapping
        }

        $filters[] = [
            'type' => 'select',
            'column' => (string) $columnIndex++, // Seller
            'label' => translate('User Role'),
            'options' => [
                ['value' => translate('Yes'), 'label' => translate('Seller')],
                ['value' => translate('No'), 'label' => translate('User')]
            ]
        ];

        $filters[] = [
            'type' => 'select',
            'column' => (string) $columnIndex++, // Status
            'label' => translate('Account Status'),
            'options' => [
                ['value' => translate('Active'), 'label' => translate('Active')],
                ['value' => translate('Suspended'), 'label' => translate('Suspended')]
            ]
        ];

        if (@settings('actions')->id_verification) {
            $filters[] = [
                'type' => 'select',
                'column' => (string) $columnIndex++, // ID Verification
                'label' => translate('ID Verification'),
                'options' => [
                    ['value' => translate('Verified'), 'label' => translate('Verified')],
                    ['value' => translate('Unverified'), 'label' => translate('Unverified')]
                ]
            ];
        }

        $filters[] = [
            'type' => 'select',
            'column' => (string) $columnIndex++, // Email Status
            'label' => translate('Email Status'),
            'options' => [
                ['value' => translate('Verified'), 'label' => translate('Verified')],
                ['value' => translate('Unverified'), 'label' => translate('Unverified')]
            ]
        ];

        $filters[] = [
            'type' => 'daterange',
            'column' => (string) $columnIndex++, // Registered Date
            'label' => translate('Registered')
        ];

        return $filters;
    }

    /**
     * Get user statistics counters with percentage changes (rolling 7-day window).
     *
     * @return array<string, mixed>
     */
    private function getUserCounters(): array
    {
        // Current metrics
        $counters = [
            'total_users' => User::count(),
            'total_sellers' => User::seller()->count(),
            'active_users' => User::active()->count(),
            'id_verified' => User::idVerified()->count(),
            'email_verified' => User::emailVerified()->count(),
            'premium_users' => User::whereHas('premium', function ($q) {
                $q->active()->where(function ($sq) {
                    $sq->whereNull('expiry_at')->orWhere('expiry_at', '>', now());
                });
            })->count(),
        ];

        // Metrics from exactly 7 days ago for comparison
        $sevenDaysAgo = now()->subDays(7);

        $previous = [
            'total' => User::where('created_at', '<', $sevenDaysAgo)->count(),
            'sellers' => User::seller()->where('created_at', '<', $sevenDaysAgo)->count(),
            'active' => User::active()->where('created_at', '<', $sevenDaysAgo)->count(),
            'id_verified' => User::idVerified()->where('created_at', '<', $sevenDaysAgo)->count(),
            'email' => User::emailVerified()->where('created_at', '<', $sevenDaysAgo)->count(),
            'premium' => User::whereHas('premium', function ($q) use ($sevenDaysAgo) {
                $q->active()->where('created_at', '<', $sevenDaysAgo)
                    ->where(function ($sq) use ($sevenDaysAgo) {
                        $sq->whereNull('expiry_at')->orWhere('expiry_at', '>', $sevenDaysAgo);
                    });
            })->count(),
        ];

        // Calculate growth percentages
        $map = [
            'total_users_percent' => [$counters['total_users'], $previous['total']],
            'total_sellers_percent' => [$counters['total_sellers'], $previous['sellers']],
            'active_users_percent' => [$counters['active_users'], $previous['active']],
            'id_verified_percent' => [$counters['id_verified'], $previous['id_verified']],
            'email_verified_percent' => [$counters['email_verified'], $previous['email']],
            'premium_users_percent' => [$counters['premium_users'], $previous['premium']],
        ];

        foreach ($map as $key => [$current, $prev]) {
            if ($prev > 0) {
                $counters[$key] = (int) round((($current - $prev) / $prev) * 100);
            } else {
                $counters[$key] = $current > 0 ? 100 : 0;
            }
        }

        return $counters;
    }

    /**
     * Get common data for user management views.
     *
     * @param User $user
     * @return array<string, mixed>
     */
    private function getData(User $user): array
    {
        $user = User::where('id', $user->id)
            ->withCount([
                'purchases' => function ($query) {
                    $query->active();
                },
                'products',
                'refunds',
                'refundsAsSeller' => function ($query) {
                    $query->accepted();
                }
            ])
            ->firstOrFail();

        $counters = [
            'total_payout_amount' => $user->payouts()->sum('amount'),
            'total_transactions_amount' => $user->transactions()->paid()->sum('total'),
        ];

        return [
            'user' => $user,
            'counters' => $counters,
        ];
    }

    /**
     * Load tab content via AJAX or redirect to edit page.
     *
     * @param User $user
     * @param string $partial
     * @return RedirectResponse|View
     */
    /**
     * Get tab data for user management views.
     *
     * @param User $user
     * @return array<string, mixed>
     */
    private function getTabData(User $user): array
    {
        $data = $this->getData($user);
        $data['payoutMethods'] = PayoutMethod::active()->get();
        $data['badges'] = Badge::all();
        $data['userBadges'] = UserBadge::where('user_id', $user->id)->with('badge')->get();
        $data['loginLogs'] = DB::table('user_login_activities')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();
        $data['premiumPlans'] = PremiumPlan::where('is_active', true)->get();
        $data['referrals'] = Referral::where('seller_id', $user->id)
            ->with('user')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Paginate activities for the details/overview tab
        $data['activities'] = $this->paginateActivities($user);

        return $data;
    }

    /**
     * Paginate user activities from multiple models.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    private function paginateActivities(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $activities = collect();
        $page = request()->get('page', 1);

        // 1. Products (if seller)
        if ($user->isSeller()) {
            $products = Product::where('seller_id', $user->id)
                ->latest()
                ->limit(50)
                ->get();
            foreach ($products as $product) {
                $activities->push([
                    'type' => 'product_added',
                    'icon' => 'bi-box-seam',
                    'color' => 'success',
                    'title' => translate('Product Added'),
                    'description' => $product->name,
                    'time' => $product->created_at,
                    'meta' => null,
                ]);
            }

            // 2. Sales
            $sales = Sale::where('seller_id', $user->id)
                ->with(['product', 'user'])
                ->latest()
                ->limit(50)
                ->get();
            foreach ($sales as $sale) {
                $activities->push([
                    'type' => 'product_sold',
                    'icon' => 'bi-cart-check',
                    'color' => 'primary',
                    'title' => translate('Product Sold'),
                    'description' => $sale->product->name ?? translate('Product'),
                    'time' => $sale->created_at,
                    'meta' => [
                        'buyer' => $sale->user->full_name ?? translate('Customer'),
                        'amount' => getAmount($sale->price),
                    ],
                ]);
            }
        }

        // 3. Purchases
        $purchases = Purchase::where('user_id', $user->id)
            ->with('sale.product')
            ->latest()
            ->limit(50)
            ->get();
        foreach ($purchases as $purchase) {
            $activities->push([
                'type' => 'purchase',
                'icon' => 'bi-bag-check',
                'color' => 'info',
                'title' => translate('Product Purchased'),
                'description' => $purchase->sale->product->name ?? translate('Product'),
                'time' => $purchase->created_at,
                'meta' => [
                    'amount' => getAmount($purchase->sale->price ?? 0),
                ],
            ]);
        }

        // 4. Tickets
        $tickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();
        foreach ($tickets as $ticket) {
            $activities->push([
                'type' => 'ticket',
                'icon' => 'bi-ticket-perforated',
                'color' => 'secondary',
                'title' => translate('Support Ticket Created'),
                'description' => $ticket->subject,
                'time' => $ticket->created_at,
                'meta' => ['ID' => '#' . $ticket->id],
            ]);
        }

        // Sort by time descending
        $sorted = $activities->sortByDesc('time');

        // Manual Pagination
        $offset = (intval($page) - 1) * $perPage;
        $items = $sorted->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Redirect to edit page (all tabs are loaded there).
     *
     * @param User $user
     * @param string $tab
     * @return RedirectResponse
     */
    private function redirectToEdit(User $user, string $tab = 'account'): RedirectResponse
    {
        return redirect()->route('admin.roles.users.edit', ['user' => $user->id, 'tab' => $tab]);
    }
}
