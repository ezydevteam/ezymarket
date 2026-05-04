<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Premium;

use App\Enums\PremiumPlanInterval;
use App\Http\Controllers\Controller;
use App\Models\Premium\PremiumPlan;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    use HandlesValidation, HandlesSorting;

    public function index(Request $request): View
    {
        $query = PremiumPlan::query()->withCount('premiums');

        if (request()->filled('plan')) {
            $query->where('id', request('plan'));
        }

        $premiumPlans = $query->get();

        return view('admin.premium.plans.index', compact('premiumPlans'));
    }

    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, PremiumPlan::class);
    }

    public function createPlan(Request $request): JsonResponse
    {
        $validator = $this->validateRequest($request, $this->getValidationRules(true));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->preparePlanData($request);

        if ($validationError = $this->validateBusinessRules($data)) {
            return response()->json(['message' => $validationError->getData()['error']], 422);
        }

        DB::beginTransaction();
        try {
            $premiumPlan = PremiumPlan::create($data);

            $this->handleFeaturedPlan($premiumPlan);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => translate('Plan created successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create plan: ' . $e->getMessage()], 500);
        }
    }

    public function updatePlan(Request $request, PremiumPlan $premiumPlan): JsonResponse
    {

        $validator = $this->validateRequest($request, $this->getValidationRules(false));

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $this->preparePlanData($request, false);

        if ($validationError = $this->validateBusinessRules($data)) {
            return response()->json(['message' => $validationError->getData()['error']], 422);
        }

        DB::beginTransaction();
        try {
            $premiumPlan->update($data);

            $this->handleFeaturedPlan($premiumPlan);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => translate('Plan updated successfully')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update premium plan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(PremiumPlan $premiumPlan): RedirectResponse
    {
        if ($premiumPlan->premiums()->exists()) {
            return $this->errorBack('This Plan has active membership and cannot be deleted');
        }

        $premiumPlan->delete();
        return $this->deletedBack();
    }

    /**
     * Bulk delete premium plans
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                DB::beginTransaction();
                try {
                    $plans = PremiumPlan::whereIn('id', $ids)->get();
                    $deletedCount = 0;
                    $skippedCount = 0;

                    foreach ($plans as $plan) {
                        if ($plan->premiums()->exists()) {
                            $skippedCount++;
                            continue;
                        }
                        $plan->delete();
                        $deletedCount++;
                    }

                    DB::commit();

                    $message = translate(':count plan(s) deleted successfully', ['count' => $deletedCount]);
                    if ($skippedCount > 0) {
                        $message .= ' ' . translate(':count plan(s) skipped (has active memberships)', ['count' => $skippedCount]);
                    }

                    return ['message' => $message, 'count' => $deletedCount];
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            },
            PremiumPlan::class,
            ':count plan(s) deleted successfully',
            'Failed to delete plans'
        );
    }

    /**
     * Bulk deactivate premium plans
     */
    public function bulkInactive(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $plans = PremiumPlan::whereIn('id', $ids)
                    ->where('is_active', true)
                    ->get();

                if ($plans->isEmpty()) {
                    throw new \Exception(translate('No active plans found to deactivate'));
                }

                foreach ($plans as $plan) {
                    $plan->update(['is_active' => false]);
                }

                return count($plans);
            },
            PremiumPlan::class,
            'Successfully deactivated :count plan(s)',
            'Error deactivating plans'
        );
    }

    /**
     * Bulk activate premium plans
     */
    public function bulkActive(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $plans = PremiumPlan::whereIn('id', $ids)
                    ->where('is_active', false)
                    ->get();

                if ($plans->isEmpty()) {
                    throw new \Exception(translate('No inactive plans found to activate'));
                }

                foreach ($plans as $plan) {
                    $plan->update(['is_active' => true]);
                }

                return count($plans);
            },
            PremiumPlan::class,
            'Successfully activated :count plan(s)',
            'Error activating plans'
        );
    }

    /**
     * Get validation rules for premium plan creation/update
     */
    private function getValidationRules(bool $isCreating = true): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'regex:/^\d*(\.\d{2})?$/'],
            'seller_earning_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'downloads' => ['nullable', 'integer', 'min:1'],
            'custom_features.*' => ['required', 'string', 'max:255'],
        ];

        if ($isCreating) {
            $rules['interval'] = ['required', 'integer', 'in:' . implode(',', array_column(PremiumPlanInterval::cases(), 'value'))];
        }

        return $rules;
    }

    /**
     * Prepare premium plan data from request
     */
    private function preparePlanData(Request $request, bool $includeInterval = true): array
    {
        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->filled('price') ? $request->input('price') : null,
            'seller_earning_percentage' => $request->input('seller_earning_percentage'),
            'downloads' => $request->filled('downloads') ? $request->input('downloads') : null,
            'custom_features' => $request->has('custom_features') ? array_values(array_filter((array) $request->input('custom_features'))) : [],
            'is_active' => $request->boolean('status'),
            'is_featured' => $request->boolean('featured'),
            'featured_label' => $request->input('featured_label'),
        ];

        if ($includeInterval) {
            $data['interval'] = $request->integer('interval');
        }

        return $data;
    }

    /**
     * Validate business rules for premium plan
     */
    private function validateBusinessRules(array $data): ?RedirectResponse
    {
        // Validate seller earnings for free premium plans
        if (!$data['price'] && $data['seller_earning_percentage'] > 0) {
            return $this->errorBackWithInput('Sellers cannot receive earnings from free plans');
        }

        // Validate minimum seller earnings for paid premium plans
        if ($data['price'] > 0) {
            $sellerEarning = ($data['price'] * $data['seller_earning_percentage']) / 100;
            if ($sellerEarning < 0.01) {
                return $this->errorBackWithInput('Seller earnings must be at least 0.01 or set percentage to 0%');
            }
        }

        return null;
    }

    /**
     * Handle featured plan logic
     */
    private function handleFeaturedPlan(PremiumPlan $premiumPlan): void
    {
        if ($premiumPlan->isFeatured()) {
            PremiumPlan::where('interval', $premiumPlan->interval->value)
                ->where('id', '!=', $premiumPlan->id)
                ->update(['is_featured' => false]);
        }
    }
}
