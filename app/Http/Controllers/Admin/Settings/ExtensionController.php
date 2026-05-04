<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Extension;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, RedirectResponse, JsonResponse};
use Illuminate\Support\Str;

/**
 * Extension Controller
 *
 * Manages third-party service integrations and extensions configuration.
 * Handles enabling/disabling extensions and updating their API credentials.
 *
 * @package App\Http\Controllers\Admin\Settings
 */
class ExtensionController extends Controller
{
    use HandlesValidation;
    /**
     * Display a listing of all extensions.
     *
     * @return View
     */
    public function index(): View
    {
        $extensions = Extension::all();

        return view('admin.settings.extensions.index', compact('extensions'));
    }

    /**
     * Show the form for editing the specified extension.
     *
     * @param Extension $extension
     * @return View
     */
    public function edit(Extension $extension): View
    {
        return view('admin.settings.extensions.edit', compact('extension'));
    }

    /**
     * Update the specified extension in storage.
     *
     * @param Request $request
     * @param Extension $extension
     * @return RedirectResponse
     */
    public function update(Request $request, Extension $extension): RedirectResponse
    {
        // Validate credentials structure
        $credentials = $request->input('credentials', []);
        $existingCredentials = (array) $extension->credentials;

        // Check if all credential keys are valid
        foreach ($credentials as $key => $value) {
            if (!array_key_exists($key, $existingCredentials)) {
                return $this->errorBackWithInput('Invalid credential parameter: :key', ['key' => $key]);
            }
        }

        // Determine status - convert to boolean (1 = true, 0 = false)
        $isActive = (bool) $request->input('is_active', 0);

        // If activating, ensure all required credentials are provided
        if ($isActive && count($credentials) > 0) {
            foreach ($credentials as $key => $value) {
                if (empty($value)) {
                    $fieldName = Str::title(str_replace('_', ' ', $key));
                    return $this->errorBackWithInput(':key cannot be empty', ['key' => $fieldName]);
                }
            }
        }

        // Update extension
        $extension->update([
            'is_active' => $isActive,
            'credentials' => $credentials,
        ]);

        // Apply credentials to environment if active
        if ($extension->isActive()) {
            $extension->applyCredentials();
        }

        return $this->updatedBack('Extension updated successfully');
    }

    /**
     * Change the status of an extension via AJAX.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:extensions,id',
            'is_active' => 'required|boolean',
        ]);

        $extension = Extension::findOrFail($request->id);
        $newStatus = (bool) $request->is_active;

        // If enabling, check if credentials are configured
        if ($newStatus) {
            $credentials = (array) $extension->credentials;
            $hasEmptyCredentials = false;

            foreach ($credentials as $key => $value) {
                if (empty($value)) {
                    $hasEmptyCredentials = true;
                    break;
                }
            }

            if ($hasEmptyCredentials) {
                return response()->json([
                    'success' => false,
                    'message' => translate('Please configure extension credentials before enabling'),
                ], 422);
            }
        }

        // Update status
        $extension->update(['is_active' => $newStatus]);

        // Apply or remove credentials
        if ($extension->isActive()) {
            $extension->applyCredentials();
        }

        $statusText = $newStatus ? translate('enabled') : translate('disabled');

        return response()->json([
            'success' => true,
            'message' => translate('Extension :status successfully', ['status' => $statusText]),
        ]);
    }
}


















