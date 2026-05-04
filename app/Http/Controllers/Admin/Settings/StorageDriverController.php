<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\StorageDriver;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StorageDriverController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of storage drivers.
     *
     * @return View
     */
    public function index(): View
    {
        $storageDrivers = StorageDriver::all();

        return view('admin.settings.storage-drivers', compact('storageDrivers'));
    }

    /**
     * Update the storage driver configuration.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $storageDriver = StorageDriver::where('alias', $request->storage_driver)
            ->firstOrFail();

        if (!$storageDriver->isLocal()) {
            // Check if credentials are provided
            if (!$request->has('credentials') || !isset($request->credentials[$storageDriver->alias])) {
                return $this->errorBack('Credentials are required for this storage driver');
            }

            $credentials = $request->credentials[$storageDriver->alias];

            foreach ($credentials as $key => $value) {
                if (!array_key_exists($key, (array) $storageDriver->credentials)) {
                    return $this->errorBack('Credentials mismatch');
                }
            }

            $storageDriver->credentials = $credentials;
            $storageDriver->update();
            $storageDriver->handler::setCredentials($storageDriver->credentials);
        }

        setEnv('FILESYSTEM_DRIVER', $storageDriver->alias);

        return $this->updatedBack();
    }

    /**
     * Test the storage driver connection.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function connectionTest(Request $request): RedirectResponse
    {
        $defaultStorage = config('filesystems.default');

        if ($defaultStorage != "local") {
            try {
                $disk = Storage::disk($defaultStorage);
                $upload = $disk->put('test.txt', 'public');

                if (!$upload) {
                    return $this->errorBack('Connection Failed');
                }

                $disk->delete('test.txt');

                return $this->successBack('Connected successfully');
            } catch (Exception $e) {
                return $this->errorBack('Connection Failed');
            }
        }

        return $this->warningBack('Local storage does not require connection test');
    }
}

















