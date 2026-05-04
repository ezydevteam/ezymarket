<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\PayoutMethod;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Contracts\View\View;;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Validation\Rule;

class PayoutMethodController extends Controller
{
    use HandlesValidation, HandlesSorting;
    /**
     * Display a listing of payout methods.
     */
    public function index(): View
    {
        $payoutMethods = PayoutMethod::all();

        return view('admin.financial.payout-methods.index', compact('payoutMethods'));
    }

    /**
     * Handle sortable reordering of payout methods.
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, PayoutMethod::class);
    }

    /**
     * Create a new payout method.
     */
    public function create(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        PayoutMethod::create($this->prepareData($validator->validated(), $request));

        return $this->successJson('Payout method created successfully');
    }

    /**
     * Update the specified payout method.
     */
    public function update(Request $request, PayoutMethod $payoutMethod): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules($payoutMethod));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $payoutMethod->update($this->prepareData($validator->validated(), $request));

        return $this->successJson('Payout method updated successfully');
    }

    /**
     * Remove the specified payout method.
     */
    public function destroy(PayoutMethod $payoutMethod): JsonResponse
    {
        $payoutMethod->delete();

        return $this->successJson('Payout method deleted successfully');
    }

    /**
     * Mark multiple payout methods as active.
     */
    public function bulkActive(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $methods = PayoutMethod::whereIn('id', $ids)->get();
                $methodsToActivate = $methods->filter(fn($m) => !$m->isActive());

                if ($methodsToActivate->isEmpty()) {
                    throw new \Exception(translate('No inactive payout methods found to activate'));
                }

                PayoutMethod::whereIn('id', $methodsToActivate->pluck('id'))->update(['is_active' => true]);
                return $methodsToActivate->count();
            },
            PayoutMethod::class,
            ':count payout method(s) have been activated successfully',
            'Failed to activate payout methods'
        );
    }

    /**
     * Mark multiple payout methods as inactive.
     */
    public function bulkInactive(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $methods = PayoutMethod::whereIn('id', $ids)->get();
                $methodsToDeactivate = $methods->filter(fn($m) => $m->isActive());

                if ($methodsToDeactivate->isEmpty()) {
                    throw new \Exception(translate('No active payout methods found to deactivate'));
                }

                PayoutMethod::whereIn('id', $methodsToDeactivate->pluck('id'))->update(['is_active' => false]);
                return $methodsToDeactivate->count();
            },
            PayoutMethod::class,
            ':count payout method(s) have been deactivated successfully',
            'Failed to deactivate payout methods'
        );
    }

    /**
     * Delete multiple payout methods.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $methods = PayoutMethod::whereIn('id', $ids)->get();

                if ($methods->isEmpty()) {
                    throw new \Exception(translate('No valid payout methods to delete'));
                }

                PayoutMethod::destroy($ids);
                return $methods->count();
            },
            PayoutMethod::class,
            ':count payout method(s) have been deleted successfully',
            'Failed to delete payout methods'
        );
    }

    /**
     * Get validation rules for payout method.
     */
    private function getValidationRules(?PayoutMethod $payoutMethod = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('payout_methods', 'name')->ignore($payoutMethod?->id)],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'gt:min_amount'],
            'monthly_limit' => ['nullable', 'integer', 'min:1'],
            'fees_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'fees_value' => ['nullable', 'numeric', 'min:0'],
            'instructions' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Prepare data for create/update.
     */
    private function prepareData(array $validated, Request $request): array
    {
        $data = [
            'name' => $validated['name'],
            'amount_limit' => [
                'min' => $validated['min_amount'],
                'max' => $validated['max_amount'] ?? null,
            ],
            'monthly_limit' => $validated['monthly_limit'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'sort_id' => $request->input('sort_id') ?? (PayoutMethod::count() + 1),
            'is_active' => $request->boolean('is_active', false),
        ];

        // Handle fees configuration
        if ($request->filled('fees_type') && $request->filled('fees_value')) {
            $data['fees'] = [
                'type' => $validated['fees_type'],
                'value' => $validated['fees_value'],
            ];
        } else {
            $data['fees'] = null;
        }

        return $data;
    }
}
