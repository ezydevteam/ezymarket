<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Captcha;
use App\Traits\HandlesValidation;
use Illuminate\Http\{RedirectResponse, JsonResponse, Request};
use Illuminate\View\View;

class CaptchaController extends Controller
{
    use HandlesValidation;
    /**
     * Display all captcha providers.
     */
    public function index(): View
    {
        $captchas = Captcha::all();

        return view('admin.settings.captcha.index', compact('captchas'));
    }

    /**
     * Show the form for editing a captcha provider.
     */
    public function edit(Captcha $captchaProvider): View
    {
        return view('admin.settings.captcha.edit', compact('captchaProvider'));
    }

    /**
     * Update the specified captcha provider.
     */
    public function update(Request $request, Captcha $captchaProvider): RedirectResponse
    {
        $request->validate([
            'site_key' => ['required', 'string', 'max:255'],
            'secret_key' => ['required', 'string', 'max:255'],
        ], [
            'site_key.required' => translate('Site key is required'),
            'site_key.max' => translate('Site key cannot exceed 255 characters'),
            'secret_key.required' => translate('Secret key is required'),
            'secret_key.max' => translate('Secret key cannot exceed 255 characters'),
        ]);

        $captchaProvider->update([
            'site_key' => $request->site_key,
            'secret_key' => $request->secret_key,
            'is_active' => (bool) $request->input('is_active', 0),
        ]);

        return $this->updatedBack('Captcha provider updated successfully');
    }

    /**
     * Set a captcha provider as default.
     */
    public function makeDefault(Captcha $captchaProvider): JsonResponse
    {
        abort_if($captchaProvider->isDefault(), 401);

        if (!$captchaProvider->isActive()) {
            return $this->errorJson('The selected captcha provider is not active');
        }

        // Remove default flag from all providers
        Captcha::where('is_default', true)->update(['is_default' => false]);

        // Set selected provider as default
        $captchaProvider->update(['is_default' => true]);

        return $this->successJson('The default captcha provider has been updated');
    }
}
