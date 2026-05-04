<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Str;
use Illuminate\View\View;

class GeneralController extends Controller
{
    use HandlesValidation;

    /**
     * Show General Settings Page
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.settings.general');
    }

    /**
     * Update General Settings
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'general.site_name' => ['required', 'string', 'block_patterns', 'max:255'],
            'general.site_url' => ['required', 'block_patterns', 'url'],
            'general.contact_email' => ['nullable', 'string', 'email', 'block_patterns'],
            'general.date_format' => ['required', 'in:' . implode(',', array_keys(Settings::dateFormats()))],
            'general.timezone' => ['required', 'in:' . implode(',', array_keys(Settings::timezones()))],
            'social_links.*' => ['nullable', 'string', 'block_patterns', 'max:50'],
            'links.*' => ['nullable', 'string', 'block_patterns'],
            'seo.title' => ['nullable', 'string', 'block_patterns', 'max:70'],
            'seo.description' => ['nullable', 'string', 'block_patterns', 'max:150'],
            'seo.keywords' => ['nullable', 'string', 'block_patterns', 'max:200'],
        ];

        $validated = $this->validateRequestWithInput($request, $rules);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        if ($request->has('actions.email_verification') && !@settings('mail')->status) {
            return $this->errorBackWithInput('Mail server is not enabled');
        }

        $requestData = $request->except('_token');

        if ($request->has('actions.contact_page') && empty($requestData['general']['contact_email'])) {
            return $this->errorBackWithInput('Contact email is required to enable contact page');
        }

        $requestData['actions'] = [];
        foreach (@settings('actions') as $key => $value) {
            $requestData['actions'][$key] = ($request->has("actions.$key")) ? 1 : 0;
        }

        foreach ($requestData as $key => $value) {
            $update = Settings::updateSettings($key, $value);
            if (!$update) {
                return $this->errorBack('Updated Error');
            }
        }

        setEnv('APP_NAME', Str::slug($requestData['general']['site_name'], '_'));
        setEnv('APP_URL', $requestData['general']['site_url']);
        setEnv('APP_TIMEZONE', $requestData['general']['timezone'], true);

        return $this->updatedBack();
    }
}
