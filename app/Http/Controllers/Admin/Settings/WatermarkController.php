<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WatermarkController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        return view('admin.settings.watermark');
    }

    /**
     * Update the watermark settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->validateRequest($request, [
            'watermark.image' => ['nullable', 'image', 'mimes:png'],
            'watermark.position' => ['required', 'string', 'in:' . implode(',', array_keys(Settings::watermarkOptions()))],
            'watermark.width' => ['required', 'integer', 'min:25', 'max:10000'],
            'watermark.height' => ['required', 'integer', 'min:25', 'max:10000'],
            'watermark.rotate' => ['required', 'integer'],
            'watermark.opacity' => ['required', 'integer', 'min:5', 'max:100'],
        ])->validate();

        $requestData = $request->except('_token');
        $currentSettings = settings('watermark');

        if ($request->hasFile('watermark.image')) {
            $image = imageUpload(
                $request->file('watermark.image'),
                'images/watermark/',
                null,
                null,
                $currentSettings->image ?? null
            );
            $requestData['watermark']['image'] = $image;
        } else {
            $requestData['watermark']['image'] = $currentSettings->image ?? null;
        }

        $requestData['watermark']['status'] = $request->has('watermark.status') ? 1 : 0;

        $update = Settings::updateSettings('watermark', $requestData['watermark']);

        if (!$update) {
            return $this->errorBack('Updated Error');
        }

        return $this->updatedBack();
    }
}
