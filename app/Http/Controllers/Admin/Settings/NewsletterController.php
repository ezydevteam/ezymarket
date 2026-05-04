<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Class NewsletterController
 * @package App\Http\Controllers\Admin\Settings
 */
class NewsletterController extends Controller
{
    use HandlesValidation;

    /**
     * Display the newsletter settings page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.settings.newsletter');
    }

    /**
     * Update the newsletter settings.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'newsletter.popup_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg'],
            'newsletter.popup_reminder_time' => ['required', 'integer', 'min:1', 'max:8760'],
            'newsletter.api_key' => ['nullable', 'string', 'max:150'],
            'newsletter.audience_id' => ['nullable', 'string', 'max:150'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $requestData = $request->except('_token');
        $newsletter = $requestData['newsletter'];

        if ($request->hasFile('newsletter.popup_image')) {
            $popupImage = imageUpload($request->file('newsletter.popup_image'), 'images/newsletter/', null, null, @settings('newsletter')->popup_image);
            $newsletter['popup_image'] = $popupImage;
        } else {
            $newsletter['popup_image'] = @settings('newsletter')->popup_image;
        }

        $newsletter['status'] = $request->has('newsletter.status') ? 1 : 0;
        $newsletter['popup_status'] = $request->has('newsletter.popup_status') ? 1 : 0;
        $newsletter['footer_status'] = $request->has('newsletter.footer_status') ? 1 : 0;
        $newsletter['register_new_users'] = $request->has('newsletter.register_new_users') ? 1 : 0;

        $update = Settings::updateSettings('newsletter', $newsletter);

        if (!$update) {
            return $this->errorBack('Updated Error');
        }

        return $this->updatedBack();
    }
}
