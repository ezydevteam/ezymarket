<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\{BuyerTax, SellerTax};
use App\Traits\HandlesValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\{Request, JsonResponse};

class TaxController extends Controller
{
    use HandlesValidation;

    /**
     * Valid tax types
     */
    private const VALID_TAX_TYPES = ['buyer', 'seller'];

    /**
     * Display a listing of taxes for a specific type.
     */
    public function index(Request $request): View
    {
        $type = $this->validateAndGetTaxType($request);
        $buyerTaxes = BuyerTax::all();
        $sellerTaxes = SellerTax::all();

        return view('admin.financial.buyer-taxes.index', compact('type', 'buyerTaxes', 'sellerTaxes'));
    }

    /**
     * Store a newly created tax.
     */
    public function store(Request $request): JsonResponse
    {
        $type = $this->validateAndGetTaxType($request);

        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();

        // Validate countries for duplicates
        if ($error = $this->validateCountryDuplicates($validated['countries'], $type)) {
            return $this->errorJson($error, [], 422);
        }

        $this->getTaxModel($type)::create($this->prepareTaxData($validated));

        return $this->successJson('Tax created successfully');
    }

    /**
     * Update the specified tax.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $type = $this->validateAndGetTaxType($request);
        $tax = $this->getTaxModel($type)::findOrFail($id);

        $validator = $this->validateRequestJson($request, $this->getValidationRules());

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $validated = $validator->validated();

        // Validate countries for duplicates (excluding current tax)
        if ($error = $this->validateCountryDuplicates($validated['countries'], $type, $id)) {
            return $this->errorJson($error, [], 422);
        }

        $tax->update($this->prepareTaxData($validated));

        return $this->successJson('Tax updated successfully');
    }

    /**
     * Remove the specified tax.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $type = $this->validateAndGetTaxType($request);
        $tax = $this->getTaxModel($type)::findOrFail($id);

        $tax->delete();

        return $this->successJson('Tax deleted successfully');
    }

    /**
     * Bulk delete taxes.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $type = $this->validateAndGetTaxType($request);
        $model = $this->getTaxModel($type);

        return $this->handleBulkAction(
            $request,
            function ($ids) use ($model) {
                $taxes = $model::whereIn('id', $ids)->get();

                if ($taxes->isEmpty()) {
                    throw new \Exception(translate('No taxes found to delete'));
                }

                foreach ($taxes as $tax) {
                    $tax->delete();
                }

                return $taxes->count();
            },
            $model,
            ':count tax(es) have been deleted successfully',
            'Failed to delete taxes'
        );
    }

    /**
     * Validate and get tax type from request.
     */
    private function validateAndGetTaxType(Request $request): string
    {
        $type = $request->route('type');

        if (!in_array($type, self::VALID_TAX_TYPES, true)) {
            abort(404, 'Invalid tax type');
        }

        return $type;
    }

    /**
     * Get the appropriate tax model based on type.
     */
    private function getTaxModel(string $type): string
    {
        return $type === 'buyer' ? BuyerTax::class : SellerTax::class;
    }

    /**
     * Get validation rules for tax.
     */
    private function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'block_patterns', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'countries' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * Prepare tax data for create/update.
     */
    private function prepareTaxData(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'percentage' => $validated['percentage'],
            'countries' => $validated['countries'],
        ];
    }

    /**
     * Validate countries and check for duplicates.
     *
     * @param array $countries Countries to validate
     * @param string $type Tax type (buyer/seller)
     * @param int|null $excludeId Tax ID to exclude from duplicate check
     * @return string|null Error message or null if valid
     */
    private function validateCountryDuplicates(array $countries, string $type, ?int $excludeId = null): ?string
    {
        $model = $this->getTaxModel($type);
        $availableCountries = countries();

        foreach ($countries as $countryCode) {
            // Check if country exists in the countries list
            if (!array_key_exists($countryCode, $availableCountries)) {
                return translate('Invalid Country');
            }

            // Check if country already exists in another tax
            $exists = $model::whereJsonContains('countries', $countryCode)
                ->when($excludeId, fn($query) => $query->where('id', '!=', $excludeId))
                ->exists();

            if ($exists) {
                return translate(':country is already exists', [
                    'country' => $availableCountries[$countryCode]
                ]);
            }
        }

        return null;
    }
}
