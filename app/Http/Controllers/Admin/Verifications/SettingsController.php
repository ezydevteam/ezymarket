<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Verifications;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse};

/**
 * ID Verification Settings Controller
 *
 * Manages ID verification system configuration including KYC settings,
 * document requirements, and placeholder images.
 *
 * @package App\Http\Controllers\Admin\Verifications
 */
class SettingsController extends Controller
{
    use HandlesValidation;

    /**
     * Display the ID verification settings form.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.id-verification.settings');
    }

    /**
     * Update ID verification settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'id_verification.required' => ['nullable', 'boolean'],
            'id_verification.photo_verification' => ['nullable', 'boolean'],
            'id_verification.auto_delete' => ['nullable', 'boolean'],
            'id_verification.id_front_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
            'id_verification.id_back_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
            'id_verification.passport_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
            'id_verification.selfie_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg', 'max:2048'],
        ]);

        $idVerificationSettings = $this->prepareSettings($request);

        $updated = Settings::updateSettings('id_verification', $idVerificationSettings);

        if (!$updated) {
            return $this->errorBack('Failed to update settings');
        }

        return $this->updatedBack('Settings updated successfully');
    }

    /**
     * Prepare ID verification settings data.
     *
     * @param Request $request
     * @return array
     */
    private function prepareSettings(Request $request): array
    {
        $settings = [
            'required' => (bool) $request->input('id_verification.required', 0),
            'photo_verification' => (bool) $request->input('id_verification.photo_verification', 0),
            'auto_delete' => (bool) $request->input('id_verification.auto_delete', 0),
        ];

        // Handle image uploads
        $imageFields = [
            'id_front_image' => 'id_verification.id_front_image',
            'id_back_image' => 'id_verification.id_back_image',
            'passport_image' => 'id_verification.passport_image',
            'selfie_image' => 'id_verification.selfie_image',
        ];

        foreach ($imageFields as $field => $requestKey) {
            $settings[$field] = $this->handleImageUpload($request, $requestKey, $field);
        }

        return $settings;
    }

    /**
     * Handle image upload for a specific field.
     *
     * @param Request $request
     * @param string $requestKey
     * @param string $field
     * @return string|null
     */
    private function handleImageUpload(Request $request, string $requestKey, string $field): ?string
    {
        if ($request->hasFile($requestKey)) {
            $oldImage = @settings('id_verification')->{$field};
            return imageUpload(
                $request->file($requestKey),
                'images/id-documents/',
                null,
                null,
                $oldImage
            );
        }

        return @settings('id_verification')->{$field};
    }
}
















