<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * AdvertisementController
 *
 * Handles CRUD operations for advertisements in the admin panel.
 */
class AdvertisementController extends Controller
{
    use HandlesValidation;
    /**
     * Display a listing of advertisements.
     */
    public function index(): View
    {
        $advertisements = Advertisement::all();

        return view('admin.advertisement.index', compact('advertisements'));
    }

    /**
     * Show the form for editing the specified advertisement.
     */
    public function edit(int $id): View
    {
        $advertisement = Advertisement::findOrFail($id);

        return view('admin.advertisement.edit', compact('advertisement'));
    }

    /**
     * Update the specified advertisement in storage.
     */
    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        // Validate that ad_code is provided when enabling the ad
        if ($request->has('is_active') && empty($request->ad_code)) {
            return $this->errorBack('The ad code cannot be empty!');
        }

        // Update advertisement
        $advertisement->update([
            'ad_code' => $request->ad_code,
            'is_active' => $request->has('is_active'),
        ]);

        return $this->updatedBack();
    }
}
