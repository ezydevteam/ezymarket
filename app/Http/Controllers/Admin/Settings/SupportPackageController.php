<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportPackage;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;
use Exception;

class SupportPackageController extends Controller
{
    use HandlesValidation, HandlesSorting;

    /**
     * Display a listing of the support packages.
     * @return View
     */
    public function index(Request $request): View
    {
        $supportPackages = SupportPackage::query()->get();

        return view('admin.settings.support-packages.index', compact('supportPackages'));
    }

    /**
     * Handle sorting of support packages.
     * @return JsonResponse
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, SupportPackage::class);
    }

    /**
     * Show the create modal for a new support package (Ajax).
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'name' => ['required', 'string', 'max:255', 'unique:support_packages'],
            'title' => ['required', 'string', 'max:255'],
            'days' => ['required', 'integer', 'min:1', 'unique:support_packages'],
            'rate_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'rate_fixed' => ['nullable', 'numeric', 'min:0'],
        ], [
            'days.unique' => translate('A support package with this duration already exists.')
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $isFree = $request->input('rate_percentage', 0) == 0 && $request->input('rate_fixed', 0) == 0;
            if ($isFree && SupportPackage::free()->exists()) {
                return $this->errorJson('Only one free support package can be created');
            }

            SupportPackage::create([
                'name' => $request->name,
                'title' => $request->title,
                'days' => $request->days,
                'rate' => [
                    'percentage' => $request->input('rate_percentage', 0),
                    'fixed' => $request->input('rate_fixed', 0),
                ],
            ]);

            return $this->successJson('Support package created successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the edit modal for the specified support package (Ajax).
     * @return JsonResponse
     */
    public function editModal(SupportPackage $supportPackage): JsonResponse
    {
        return response()->json([
            'title' => translate('Edit Support Package'),
            'content' => view('admin.settings.support-packages.partials.edit-modal', compact('supportPackage'))->render()
        ]);
    }

    /**
     * Update the specified support package (Ajax).
     * @return JsonResponse
     */
    public function update(Request $request, SupportPackage $supportPackage): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'name' => ['required', 'string', 'max:255', 'unique:support_packages,name,' . $supportPackage->id],
            'title' => ['required', 'string', 'max:255'],
            'days' => ['required', 'integer', 'min:1', 'unique:support_packages,days,' . $supportPackage->id],
            'rate_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'rate_fixed' => ['nullable', 'numeric', 'min:0'],
        ], [
            'days.unique' => translate('A support package with this duration already exists.')
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $isFree = $request->input('rate_percentage', 0) == 0 && $request->input('rate_fixed', 0) == 0;
            if ($isFree && SupportPackage::free()->where('id', '!=', $supportPackage->id)->exists()) {
                return $this->errorJson('Only one free support package can be created');
            }

            $supportPackage->update([
                'name' => $request->name,
                'title' => $request->title,
                'days' => $request->days,
                'rate' => [
                    'percentage' => $request->input('rate_percentage', 0),
                    'fixed' => $request->input('rate_fixed', 0),
                ],
            ]);

            return $this->successJson('Support package updated successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function destroy(SupportPackage $supportPackage): JsonResponse
    {
        if ($supportPackage->isFree()) {
            return $this->errorJson('The free support package cannot be deleted');
        }

        $supportPackage->delete();

        return $this->successJson('Support package deleted successfully');
    }
}
