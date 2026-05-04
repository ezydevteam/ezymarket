<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class ReferralController
 * @package App\Http\Controllers\Admin\Settings
 */
class ReferralController extends Controller
{
    use HandlesValidation;

    /**
     * Display the referral settings page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.settings.referral');
    }

    /**
     * Update the referral settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validation = $this->validateRequestWithoutInput($request, [
            'referral.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validation instanceof RedirectResponse) {
            return $validation;
        }

        $requestData = $request->except('_token');
        $requestData['referral']['status'] = $request->has('referral.status') ? 1 : 0;

        $update = Settings::updateSettings('referral', $requestData['referral']);

        if (!$update) {
            return $this->errorBack();
        }

        return $this->updatedBack();
    }
}
