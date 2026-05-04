<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Roles;

use App\Enums\Admin\AdminRole;
use App\Enums\Product\ProductHistoryTitle;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Hash, Mail};

/**
 * Staff Management Controller
 *
 * Handles all staff-related administrative operations including CRUD,
 * category assignments (for reviewers), password management, and 2FA.
 * Manages all admin roles: admin, manager, accountant, reviewer.
 *
 * @package App\Http\Controllers\Admin
 */
class StaffController extends Controller
{
    use HandlesValidation;

    /**
     * Display a paginated list of staff with search functionality.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var \Illuminate\Http\Request $request */

        $staff = Admin::staff()->get();
        $roles = $this->getAvailableRoles();
        $categories = ProductCategory::all();

        return view('admin.roles.staff.index', compact('staff', 'roles', 'categories'));
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return string
     */
    public function createModal(): string
    {
        $categories = ProductCategory::all();
        $roles = $this->getAvailableRoles();

        return view('admin.roles.staff.modals.modal_create', compact('categories', 'roles'))->render();
    }

    /**
     * Store a newly created staff member in the database.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Prevent creating admin role through staff management
        if ($request->role === AdminRole::ADMIN->value) {
            return $this->errorJson('Cannot create admin accounts through staff management', [], 403);
        }

        $rules = [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:6', 'alpha_dash', 'block_patterns', 'max:50', 'unique:admins'],
            'email' => ['required', 'string', 'email', 'indisposable', 'block_patterns', 'max:100', 'unique:admins'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:manager,accountant,reviewer'],
            'status' => ['required', 'boolean'],
        ];

        // Add categories validation only if role is reviewer
        if ($request->role === AdminRole::REVIEWER->value) {
            $categories = ProductCategory::all();

            $rules['categories'] = ['required', 'array'];

            foreach ($categories as $category) {
                $rules['categories.' . $category->id] = ['required', 'integer', 'exists:product_categories,id'];
            }
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $staff = Admin::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => AdminRole::from($request->role),
                'status' => $request->status,
            ]);

            if ($staff) {
                // Sync categories for reviewer role
                if ($staff->role === AdminRole::REVIEWER && $request->has('categories')) {
                    $staff->categories()->sync($request->categories);
                }

                $member_label = $staff->role->label();

                return $this->successJson('New ' . $member_label . ' created successfully');
            }

            return $this->errorJson('Failed to create staff member');
        } catch (Exception $e) {
            return $this->errorJson('Failed to create staff member');
        }
    }

    /**
     * Show the form for editing the specified staff member.
     *
     * @param Admin $staff
     * @return View
     */
    public function edit(Request $request, Admin $staff): View
    {
        $this->preventEditingAdmin($staff);

        $data = $this->getTabData($staff);
        $tab = $request->input('tab', 'account');

        // Map tabs to their partial views
        $tabsMap = [
            'account'   => 'admin.roles.staff.partials.account-content',
            'privilege' => 'admin.roles.staff.partials.privilege-content',
            'security'  => 'admin.roles.staff.partials.security-content',
        ];

        // Determine which partial to show
        $activePartial = $tabsMap[$tab] ?? 'admin.roles.staff.partials.account-content';

        if ($request->ajax()) {
            return view($activePartial, $data);
        }

        // Add active tab and partial to the view data for initial load
        $data['activeTab'] = $tab;
        $data['activePartial'] = $activePartial;

        return view('admin.roles.staff.edit', $data);
    }

    /**
     * Update the specified staff member in the database.
     *
     * @param Request $request
     * @param Admin $staff
     * @return JsonResponse
     */
    public function update(Request $request, Admin $staff): JsonResponse
    {
        $this->preventEditingAdmin($staff);
        $this->preventSelfEdit($staff);

        $validator = $this->validateRequestJson($request, [
            'firstname' => ['required', 'string', 'block_patterns', 'max:50'],
            'lastname' => ['required', 'string', 'block_patterns', 'max:50'],
            'username' => ['required', 'string', 'min:6', 'alpha_dash', 'block_patterns', 'max:50', 'unique:admins,username,' . $staff->id],
            'email' => ['required', 'email', 'indisposable', 'string', 'block_patterns', 'max:100', 'unique:admins,email,' . $staff->id],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'status' => ['required', 'boolean'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $updateData = [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'email' => $request->email,
                'status' => $request->status,
            ];

            if ($request->hasFile('avatar')) {
                $updateData['avatar'] = imageUpload(
                    $request->file('avatar'),
                    'images/avatars/admins/',
                    '120x120',
                    null,
                    $staff->avatar
                );
            }

            $staff->update($updateData);

            return $this->successJson('Staff member details updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

     /**
     * Update the staff member's status (active/inactive).
     *
     * @param Admin $staff
     * @return JsonResponse
     */
    public function updateStatus(Admin $staff): JsonResponse
    {
        try {
            $this->preventEditingAdmin($staff);
            $this->preventSelfEdit($staff);

            // Toggle status
            $newStatus = !$staff->status;

            $staff->update([
                'status' => $newStatus,
            ]);

            $message = $newStatus
                ? translate('Staff member has been activated successfully')
                : translate('Staff member has been suspended successfully');

            return $this->successJson($message);
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the categories management page for the specified staff member.
     * Redirects to edit page with all tabs loaded.
     *
     * @param Admin $staff
     * @return RedirectResponse
     */
    public function privilege(Admin $staff): RedirectResponse
    {
        $this->preventEditingAdmin($staff);

        return $this->redirectToEdit($staff);
    }

    /**
     * Update the categories for the specified staff member (reviewers only).
     *
     * @param Request $request
     * @param Admin $staff
     * @return JsonResponse
     */
    public function updatePrivilege(Request $request, Admin $staff): JsonResponse
    {
        $this->preventEditingAdmin($staff);

        // Only reviewers need category assignments
        if ($staff->role !== AdminRole::REVIEWER) {
            return $this->errorJson('Only reviewers need category assignments');
        }

        $validator = $this->validateRequestJson($request, [
            'categories' => ['required', 'array']
        ]);

        if ($request->has('categories')) {
            $categories = ProductCategory::all();

            foreach ($categories as $category) {
                $rules['categories.' . $category->id] = ['required', 'integer', 'exists:product_categories,id'];
            }
        }

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $staff->categories()->sync($request->categories);

            return $this->successJson('New categories assigned successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the password change form for the specified staff member.
     * Redirects to edit page with all tabs loaded.
     *
     * @param Admin $staff
     * @return RedirectResponse
     */
    public function security(Admin $staff): RedirectResponse
    {
        $this->preventEditingAdmin($staff);

        return $this->redirectToEdit($staff);
    }

    /**
     * Update staff member password.
     *
     * @param Request $request
     * @param Admin $staff
     * @return JsonResponse
     */
    public function updatePassword(Request $request, Admin $staff): JsonResponse
    {
        $this->preventEditingAdmin($staff);
        $this->preventSelfEdit($staff);

        $validator = $this->validateRequestJson($request, [
            'new-password' => ['required', 'string', 'min:8', 'confirmed'],
            'new-password_confirmation' => ['required'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $staff->update([
            'password' => Hash::make($request->get('new-password')),
        ]);

        return $this->successJson('Staff member password updated successfully');
    }

    /**
     * Update staff member 2FA status.
     *
     * @param Request $request
     * @param Admin $staff
     * @return JsonResponse
     */
    public function update2FA(Request $request, Admin $staff): JsonResponse
    {
        $this->preventEditingAdmin($staff);

        $google2faStatus = (bool) $request->input('google2fa_status', false);

        if ($google2faStatus && !$staff->has2fa()) {
            if ($staff->id !== authAdmin()->id) {
                return $this->errorJson('Two-Factor authentication can only be activated by the staff member themselves.');
            }

            // Enable 2FA - generate secret
            $secret = encrypt(app('pragmarx.google2fa')->generateSecretKey());
            $staff->update([
                'google2fa_status' => true,
                'google2fa_secret' => $secret,
            ]);
        } elseif (!$google2faStatus && $staff->has2fa()) {
            // Disable 2FA
            $staff->update([
                'google2fa_status' => false,
                'google2fa_secret' => null,
            ]);
        }

        return $this->successJson('Two-Factor authentication status updated successfully');
    }

    /**
     * Remove the specified staff member.
     *
     * @param Admin $staff
     * @return JsonResponse
     */
    public function destroy(Admin $staff): JsonResponse
    {
        try {
            $this->preventEditingAdmin($staff);
            $this->preventSelfEdit($staff);

            if ($staff->avatar) {
                removeFile($staff->avatar);
            }

            $staff->delete();

            return $this->successJson('Staff member deleted successfully');
        } catch (Exception $e) {
            return $this->errorJson('Failed to delete staff member');
        }
    }

    /**
     * Bulk delete staff members.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $staff = Admin::whereIn('id', $ids)->get();
                $deletedCount = 0;
                $errors = [];

                foreach ($staff as $member) {
                    try {
                        $this->preventEditingAdmin($member);
                        $this->preventSelfEdit($member);

                        if ($member->avatar) {
                            removeFile($member->avatar);
                        }

                        $member->delete();
                        $deletedCount++;
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }

                if ($deletedCount === 0 && !empty($errors)) {
                    throw new \Exception(implode(', ', $errors));
                }

                return [
                    'count' => $deletedCount,
                    'message' => $deletedCount > 0
                        ? translate(':count staff member(s) deleted successfully', ['count' => $deletedCount])
                        : translate('No staff members were deleted'),
                ];
            },
            Admin::class,
            ':count staff member(s) deleted successfully',
            'Error deleting staff members'
        );
    }

    /**
     * Send custom email to the specified staff member.
     *
     * @param Request $request
     * @param Admin $staff
     * @return JsonResponse
     */
    public function sendMail(Request $request, Admin $staff): JsonResponse
    {
        $this->preventEditingAdmin($staff);

        $validator = $this->validateRequestJson($request, [
            'subject' => ['required', 'string', 'block_patterns'],
            'reply_to' => ['required', 'email', 'block_patterns'],
            'message' => ['required', 'string'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        if (!@settings('mail')->status) {
            return $this->errorJson('Mail server is not enabled');
        }

        try {
            Mail::send([], [], function ($message) use ($request, $staff) {
                $message->to($staff->email)
                    ->replyTo($request->reply_to)
                    ->subject($request->subject)
                    ->html($request->message);

                 // Attach files if present
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                            'mime' => $file->getMimeType(),
                        ]);
                    }
                }
            });

            return $this->successJson('Email sent successfully');
        } catch (Exception $e) {
            return $this->errorJson('Failed to send email. Please try again.');
        }
    }

    /**
     * Login as the specified staff member.
     *
     * @param Admin $staff
     * @return RedirectResponse
     */
    public function login(Admin $staff): RedirectResponse
    {
        $this->preventEditingAdmin($staff);
        $this->preventSelfEdit($staff);

        // Check if staff account is active
        if (!$staff->isActive()) {
            return $this->errorBack('Cannot login as inactive staff member');
        }

        try {
             Auth::guard('admin')->login($staff);

            /** @var AdminRole $role */
            $role = $staff->role;
            $landingPage = $role->landingPage();

            return redirect()->route($landingPage)
                ->with('success', 'Logged in as ' . $staff->full_name);
        } catch (Exception $e) {
            return $this->errorBack('Failed to login as staff member');
        }
    }

    /**
     * Get common data for staff management views.
     *
     * @param Admin $staff
     * @return array<string, mixed>
     */
    private function getData(Admin $staff): array
    {
        $staff->loadCount([
            'categories',
            'productHistories as reviewed_products_count' => function ($query) {
                $query->whereIn('title', [
                    ProductHistoryTitle::SUBMISSION_APPROVED,
                    ProductHistoryTitle::RESUBMISSION_APPROVED,
                    ProductHistoryTitle::UPDATE_APPROVED,
                    ProductHistoryTitle::REVISION_REQUIRED,
                    ProductHistoryTitle::REJECTION,
                ]);
            }
        ]);

        $data = [
            'staff' => $staff,
            'roles' => $this->getAvailableRoles(),
        ];

        // Generate QR code if 2FA is enabled
        if ($staff->has2fa() && $staff->google2fa_secret) {
            try {
                $google2fa = app('pragmarx.google2fa');
                $data['qrCode'] = $google2fa->getQRCodeInline(
                    @settings('general')->site_name,
                    $staff->email,
                    decrypt($staff->google2fa_secret)
                );
            } catch (\Exception $e) {
                $data['qrCode'] = null;
            }
        } else {
            $data['qrCode'] = null;
        }

        return $data;
    }

    /**
     * Get tab data for staff management views.
     *
     * @param Admin $staff
     * @return array<string, mixed>
     */
    private function getTabData(Admin $staff): array
    {
        /** @var \App\Models\Admin $staff */

        $data = $this->getData($staff);
        $data['categories'] = ProductCategory::all();
        $data['staffCategoryIds'] = $staff->categories->pluck('id')->toArray();

        return $data;
    }

    /**
     * Redirect to edit page (all tabs are loaded there).
     *
     * @param Admin $staff
     * @return RedirectResponse
     */
    private function redirectToEdit(Admin $staff): RedirectResponse
    {
        /** @var \App\Models\Admin $staff */

        return redirect()->route('admin.roles.staff.edit', $staff->id);
    }

    /**
     * Get available roles for staff creation/management.
     *
     * @return array<string, array<string, string>>
     */
    private function getAvailableRoles(): array
    {
        return [
            AdminRole::MANAGER->value => [
                'label' => AdminRole::MANAGER->label(),
                'description' => AdminRole::MANAGER->description(),
            ],
            AdminRole::ACCOUNTANT->value => [
                'label' => AdminRole::ACCOUNTANT->label(),
                'description' => AdminRole::ACCOUNTANT->description(),
            ],
            AdminRole::REVIEWER->value => [
                'label' => AdminRole::REVIEWER->label(),
                'description' => AdminRole::REVIEWER->description(),
            ],
        ];
    }

    /**
     * Prevent editing admin role accounts.
     *
     * @param Admin $staff
     * @return void
     */
    protected function preventEditingAdmin(Admin $staff): void
    {
        // Prevent editing admin role
        abort_if($staff->role === AdminRole::ADMIN, 403, 'Cannot modify admin accounts through staff management');
    }

    /**
     * Prevent staff from editing themselves.
     *
     * @param Admin $staff
     * @return void
     */
    protected function preventSelfEdit(Admin $staff): void
    {
        // Prevent editing yourself
        abort_if($staff->id === authAdmin()->id, 404, 'Cannot edit yourself');
    }
}
