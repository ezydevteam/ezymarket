<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    use HandlesValidation;

    /* Display Maintenance Mode Settings */
    public function index(): View
    {
        return view('admin.system.maintenance');
    }

    /* Update Maintenance Mode Settings */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'maintenance.icon' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg'],
            'maintenance.title' => ['required_if:maintenance.status,on', 'nullable', 'string', 'max:150'],
            'maintenance.body' => ['required_if:maintenance.status,on', 'nullable', 'string', 'max:500'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $requestData = $request->except('_token');
        $maintenance = $requestData['maintenance'];

        if ($request->has('maintenance.icon')) {
            $icon = imageUpload($request->file('maintenance.icon'), 'images/maintenance/', null, null, @settings('maintenance')->icon);
            $maintenance['icon'] = $icon;
        } else {
            $maintenance['icon'] = @settings('maintenance')->icon;
        }

        $maintenance['status'] = ($request->has('maintenance.status')) ? 1 : 0;

        $update = Settings::updateSettings('maintenance', $maintenance);
        if (!$update) {
            return $this->errorBack('Updated Error');
        }

        return $this->updatedBack();
    }
}
