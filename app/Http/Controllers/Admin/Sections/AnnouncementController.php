<?php

namespace App\Http\Controllers\Admin\Sections;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use HandlesValidation;

    /**
     * Display the announcement settings page.
     * @return View
     */
    public function index(): View
    {
        return view('admin.sections.announcement');
    }

    /**
     * Update the announcement settings.
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'announcement.body' => ['required_if:announcement.status,on', 'nullable', 'string', 'block_patterns', 'max:500'],
            'announcement.button_title' => ['required_with:announcement.button_link', 'nullable', 'string', 'block_patterns', 'max:100'],
            'announcement.button_link' => ['required_with:announcement.button_title', 'nullable', 'string', 'block_patterns'],
            'announcement.background_color' => ['required_if:announcement.status,on', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'announcement.button_background_color' => ['required_if:announcement.status,on', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'announcement.button_text_color' => ['required_if:announcement.status,on', 'string', 'regex:/^#[A-Fa-f0-9]{6}$/'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $requestData = $request->except('_token');

        $requestData['announcement']['status'] = ($request->has('announcement.status')) ? 1 : 0;

        foreach ($requestData as $key => $value) {
            $update = Settings::updateSettings($key, $value);
            if (!$update) {
                return $this->errorBack('Updated Error');
            }
        }

        return $this->updatedBack();
    }
}


















