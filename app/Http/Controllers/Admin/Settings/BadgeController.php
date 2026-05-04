<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Classes\CountryList;
use App\Enums\BadgeAlias;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\SellerLevel;
use App\Traits\HandlesValidation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Badge Management Controller
 *
 * Handles CRUD operations for badges including country badges,
 * seller level badges, and membership year badges.
 *
 * @package App\Http\Controllers\Admin\Settings
 */
class BadgeController extends Controller
{
    use HandlesValidation;
    /**
     * Display a listing of badges.
     */
    public function index(Request $request): View
    {
        $sellerLevels = SellerLevel::all();

        $badges = Badge::query()
            ->excludePremiumIfNotLicensed()
            ->get();

        return view('admin.settings.badges.index', compact('badges', 'sellerLevels'));
    }

    /**
     * Store a newly created badge in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => ['required', 'string', 'block_patterns', 'max:255', 'unique:badges'],
            'title' => ['nullable', 'string', 'block_patterns', 'max:255'],
            'badge_image' => ['required', 'image', 'mimes:png,svg', 'max:5120'],
            'type' => ['nullable', 'string', 'in:countries,seller_levels,membership_years'],
        ];

        // Determine alias and add type-specific validation
        $alias = $this->determineAliasAndValidation($request, $rules);

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $validated = $validator->validated();
            $image = imageUpload($request->file('badge_image'), 'images/badges/');

            Badge::create([
                'name' => $validated['name'],
                'alias' => $alias,
                'title' => $validated['title'] ?? null,
                'image' => $image,
                'country' => $request->input('country'),
                'level_id' => $request->input('seller_level'),
                'membership_years' => $request->input('membership_years'),
            ]);

            return $this->successJson('Badge Created Successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Show the edit modal for the specified badge (Ajax).
     */
    public function editModal(Badge $badge): JsonResponse
    {
        $sellerLevels = SellerLevel::all();
        return response()->json([
            'title' => translate('Edit Badge'),
            'content' => view('admin.settings.badges.partials.edit-modal', compact('badge', 'sellerLevels'))->render()
        ]);
    }

    /**
     * Update the specified badge in storage.
     *
     * @param Request $request
     * @param Badge $badge
     * @return JsonResponse
     */
    public function update(Request $request, Badge $badge): JsonResponse
    {
        $validator = $this->validateRequestJson($request, [
            'name' => ['required', 'string', 'block_patterns', 'max:255', Rule::unique('badges')->ignore($badge->id)],
            'title' => ['nullable', 'string', 'block_patterns', 'max:255'],
            'badge_image' => ['nullable', 'image', 'mimes:png,svg', 'max:5120'],
        ]);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $validated = $validator->validated();

            $image = $request->hasFile('badge_image')
                ? imageUpload($request->file('badge_image'), 'images/badges/', null, null, $badge->image)
                : $badge->image;

            $badge->update([
                'name' => $validated['name'],
                'title' => $validated['title'] ?? null,
                'image' => $image,
            ]);

            return $this->successJson('Badge Updated Successfully');
        } catch (Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * Remove the specified badge from storage.
     *
     * @param Badge $badge
     * @return JsonResponse
     */
    public function destroy(Badge $badge): JsonResponse
    {
        abort_if($badge->is_permanent, 403, translate('Cannot delete default badge'));

        $badge->deleteImage();
        $badge->delete();

        return $this->successJson('Badge Deleted Successfully');
    }

    /**
     * Bulk delete multiple badges.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function (array $ids) {
                $badges = Badge::whereIn('id', $ids)
                    ->where('is_permanent', false)
                    ->get();

                if ($badges->isEmpty()) {
                    throw new \Exception(translate('Permanent badges cannot be deleted.'));
                }

                $deletedCount = 0;
                foreach ($badges as $badge) {
                    $badge->deleteImage();
                    $badge->delete();
                    $deletedCount++;
                }

                $permanentCount = count($ids) - $deletedCount;

                if ($permanentCount > 0) {
                    return [
                        'count' => $deletedCount,
                        'message' => translate(':count badge(s) deleted successfully. :skipped permanent badge(s) skipped', [
                            'count' => $deletedCount,
                            'skipped' => $permanentCount
                        ])
                    ];
                }

                return $deletedCount;
            },
            Badge::class,
            ':count badge(s) deleted successfully',
            'An error occurred while deleting badges'
        );
    }

    /**
     * Determine badge alias and add type-specific validation rules.
     */
    private function determineAliasAndValidation(Request $request, array &$rules): string
    {
        if (!$request->filled('type')) {
            return Str::slug($request->input('name'), '_');
        }

        return match ($request->input('type')) {
            'countries' => $this->addCountryValidation($rules),
            'seller_levels' => $this->addSellerLevelValidation($rules),
            'membership_years' => $this->addMembershipYearsValidation($rules),
            default => Str::slug($request->input('name'), '_'),
        };
    }

    /**
     * Add country badge validation rules.
     */
    private function addCountryValidation(array &$rules): string
    {
        $rules['country'] = [
            'required',
            'string',
            Rule::in(array_keys(CountryList::all())),
            'unique:badges,country',
        ];

        return BadgeAlias::COUNTRY->value;
    }

    /**
     * Add seller level badge validation rules.
     */
    private function addSellerLevelValidation(array &$rules): string
    {
        $rules['seller_level'] = [
            'required',
            'integer',
            'unique:badges,level_id',
            'exists:levels,id',
        ];

        return BadgeAlias::SELLER_LEVEL->value;
    }

    /**
     * Add membership years badge validation rules.
     */
    private function addMembershipYearsValidation(array &$rules): string
    {
        $rules['membership_years'] = [
            'required',
            'integer',
            'unique:badges,membership_years',
            'min:1',
        ];

        return BadgeAlias::MEMBERSHIP_YEARS->value;
    }
}
