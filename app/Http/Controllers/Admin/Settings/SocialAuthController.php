<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SocialAuth;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialAuthController extends Controller
{
    use HandlesValidation, HandlesSorting;
    /**
     * Display a listing of social authentications.
     */
    public function index(): View
    {
        $socialAuths = SocialAuth::sorted()->get();

        return view('admin.settings.social-auth.index', compact('socialAuths'));
    }

    /**
     * Update the sort order of social authentications.
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, SocialAuth::class);
    }

    /**
     * Show the form for editing the specified social authentication.
     */
    public function edit(SocialAuth $socialAuth): View
    {
        return view('admin.settings.social-auth.edit', compact('socialAuth'));
    }

    /**
     * Update the specified social authentication.
     */
    public function update(Request $request, SocialAuth $socialAuth): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'string', 'max:255'],
            'client_id' => ['nullable', 'string', 'max:255'],
            'client_secret' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'integer', Rule::in(array_keys(SocialAuth::getTypeOptions()))],
        ]);

        // Determine status - convert to boolean
        $isActive = (bool) $request->input('is_active', 0);

        // Validate credentials if status is active
        if ($isActive && (empty($request->client_id) || empty($request->client_secret))) {
            return $this->errorBackWithInput('Client ID and Client Secret are required when status is active');
        }

        $data = [
            'name' => $request->name,
            'client_id' => $request->client_id,
            'client_secret' => $request->client_secret,
            'type' => $request->type,
            'is_active' => $isActive,
        ];

        // Only include logo if it's provided
        if (!empty($request->logo)) {
            $data['logo'] = $request->logo;
        }

        $socialAuth->update($data);
        $socialAuth->setCredentials();

        return $this->updatedBack('Social authentication provider updated successfully');
    }

    /**
     * Upload logo image.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ]);

        try {
            $file = $request->file('image');
            $imagePath = imageUpload($file, 'images/social-auth/', null, null, false);

            return response()->json([
                'status' => true,
                'image' => $imagePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
