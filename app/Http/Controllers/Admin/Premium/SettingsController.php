<?php

namespace App\Http\Controllers\Admin\Premium;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class SettingsController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        return view('admin.premium.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'premium.terms_link' => ['nullable', 'string', 'max:255'],
            'premium.recommended_package_label' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->handleValidationErrorsWithInput($validator);
        }

        $data = $request->input('premium', []);
        $data['status'] = isset($data['status']) && $data['status'] ? 1 : 0;

        $update = Settings::updateSettings('premium', $data);

        if (!$update) {
            return $this->errorBackWithInput('Updated Error');
        }

        return $this->updatedBack();
    }
}
