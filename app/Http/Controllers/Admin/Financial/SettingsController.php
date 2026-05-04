<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class SettingsController extends Controller
{
    use HandlesValidation;

    /**
     * Display the financial settings page.
     */
    public function index(): View
    {
        return view('admin.financial.settings');
    }

    /**
     * Update financial settings with validation.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithInput($request, $this->getValidationRules());

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        try {
            $settingsData = $this->prepareSettingsData($request);

            foreach ($settingsData as $settingKey => $settingValue) {
                Settings::updateSettings($settingKey, $settingValue);
            }

            return $this->successBack('Updated Successfully');
        } catch (\Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    /**
     * Get validation rules.
     */
    private function getValidationRules(): array
    {
        return [
            'deposit.minimum' => ['nullable', 'numeric', 'min:0'],
            'payout.minimum' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Prepare settings data from request.
     */
    private function prepareSettingsData(Request $request): array
    {
        return [
            'deposit' => [
                'status' => (int) $request->input('deposit.status', 0),
                'minimum' => $request->input('deposit.minimum', 0),
            ],
            'payout' => [
                'status' => (int) $request->input('payout.status', 0),
                'minimum' => $request->input('payout.minimum', 0),
            ],
        ];
    }
}
