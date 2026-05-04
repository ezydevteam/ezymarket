<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\Currency;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Str;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    use HandlesValidation, HandlesSorting;

    public function index(): View
    {
        $currencies = Currency::query()->get();

        return view('admin.financial.currencies.index', compact('currencies'));
    }

    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, Currency::class);
    }

    public function makeDefault(Currency $currency): JsonResponse
    {
        abort_if($currency->isDefault(), 401);

        $this->setDefaultCurrency($currency);

        return $this->successJson('The default currency has been updated');
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            Currency::create($this->prepareData($validator->validated(), $request));

            return $this->successJson('The currency has been created successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }
    
    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validator = $this->validateRequestJson($request, $this->getValidationRules($currency));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        try {
            $currency->update($this->prepareData($validator->validated(), $request, $currency));

            return $this->successJson('The currency has been updated successfully');
        } catch (\Exception $e) {
            return $this->errorJson($e->getMessage(), [], 500);
        }
    }

    public function destroy(Currency $currency): JsonResponse
    {
        abort_if($currency->isDefault(), 401);

        removeFile(public_path($currency->icon));

        $currency->delete();

        return $this->successJson('The currency has been deleted');
    }

    /**
     * Get validation rules for currency.
     */
    private function getValidationRules(?Currency $currency = null): array
    {
        $rules = [
            'symbol' => ['required', 'string', 'block_patterns', 'max:10'],
            'country' => ['required', 'string', 'max:100'],
            'position' => ['required', 'integer', 'in:' . implode(',', array_keys(Currency::getCurrencyPositionOptions()))],
            'rate' => ['required', 'numeric', 'min:0.000001'],
        ];

        if ($currency) {
            // Update rules
            $rules['icon'] = ['nullable', 'mimes:png,jpg,jpeg,webp'];
        } else {
            // Create rules
            $rules['icon'] = ['required', 'mimes:png,jpg,jpeg,webp'];
            $rules['code'] = ['required', 'string', 'block_patterns', 'unique:currencies', 'max:10'];
        }

        return $rules;
    }

    /**
     * Prepare data for create/update.
     */
    private function prepareData(array $validated, Request $request, ?Currency $currency = null): array
    {
        $data = [
            'symbol' => $validated['symbol'],
            'country' => $validated['country'],
            'position' => $validated['position'],
            'rate' => $validated['rate'],
        ];

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $existingIcon = $currency?->icon;
            $data['icon'] = imageUpload(
                $request->file('icon'),
                'images/currencies/',
                null,
                Str::slug($validated['code'] ?? $currency->code),
                $existingIcon
            );
        } elseif ($currency) {
            $data['icon'] = $currency->icon;
        }

        // Add code only for create
        if (!$currency && isset($validated['code'])) {
            $data['code'] = $validated['code'];
        }

        return $data;
    }

    /**
     * Set currency as default
     */
    private function setDefaultCurrency(Currency $currency): void
    {
        // Remove default flag from all currencies
        Currency::where('is_default', true)->update(['is_default' => false]);

        // Set the selected currency as default
        $currency->update(['is_default' => true]);
    }
}
