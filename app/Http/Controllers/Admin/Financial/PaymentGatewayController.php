<?php

namespace App\Http\Controllers\Admin\Financial;

use App\Http\Controllers\Controller;
use App\Models\Financial\PaymentGateway;
use App\Traits\{HandlesValidation, HandlesSorting};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    use HandlesValidation, HandlesSorting;
    /**
     * Display a listing of payment gateways.
     */
    public function index(): View
    {
        $paymentGateways = PaymentGateway::query()->get();;

        return view('admin.financial.payment-gateways.index', compact('paymentGateways'));
    }

    /**
     * Update the sort order of payment gateways.
     */
    public function sortable(Request $request): JsonResponse
    {
        return $this->handleSortable($request, PaymentGateway::class);
    }

    /**
     * Update the specified payment gateway.
     */
    public function update(Request $request, PaymentGateway $paymentGateway): JsonResponse
    {
        // Validate request
        $validator = $this->validateRequestJson($request, $this->getValidationRules($paymentGateway));

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        // Prepare and validate update data
        $updateData = $this->prepareUpdateData($request, $paymentGateway);

        $validationError = $this->validateBusinessRules($updateData, $paymentGateway);
        if ($validationError) {
            return $this->errorJson($validationError);
        }

        // Update payment gateway
        $paymentGateway->update($updateData);

        return $this->successJson('Payment gateway updated successfully');
    }

    /**
     * Get validation rules based on gateway type.
     */
    private function getValidationRules(PaymentGateway $gateway): array
    {
        $rules = [
            'gateway_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'gateway_name' => ['required', 'string', 'block_patterns', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        // Fee rules for non-account-balance gateways
        if (!$gateway->isAccountBalance()) {
            $rules['gateway_fees'] = ['required', 'integer', 'min:0', 'max:100'];
        }

        // Charge currency/rate rules for API-based gateways
        if (!$gateway->isAccountBalance() && !$gateway->isManual()) {
            $rules['charge_currency'] = ['nullable', 'string', 'max:10', 'required_with:charge_rate'];
            $rules['charge_rate'] = ['nullable', 'numeric', 'min:0', 'required_with:charge_currency'];
        }

        // Mode validation
        if ($gateway->mode) {
            $rules['gateway_mode'] = ['nullable', 'string', 'in:' . implode(',', array_keys(PaymentGateway::getModes()))];
        }

        return $rules;
    }

    /**
     * Prepare data for updating the payment gateway.
     */
    private function prepareUpdateData(Request $request, PaymentGateway $gateway): array
    {
        $data = [
            'name' => $request->gateway_name,
            'is_active' => $request->boolean('is_active'),
        ];

        // Handle logo upload
        if ($request->hasFile('gateway_logo')) {
            $data['logo'] = imageUpload(
                $request->file('gateway_logo'),
                'images/payment-gateways/',
                null,
                $gateway->alias,
                $gateway->logo
            );
        }

        // Handle fees
        $data['fees'] = $gateway->isAccountBalance() ? 0 : ($request->gateway_fees ?? 0);

        // Handle charge currency and rate for API gateways
        if (!$gateway->isAccountBalance() && !$gateway->isManual()) {
            $data['charge_currency'] = $request->charge_currency;
            $data['charge_rate'] = $request->charge_rate;
        }

        // Handle credentials for API gateways
        if (!$gateway->isManual() && $request->has('gateway_credentials')) {
            $data['credentials'] = $request->gateway_credentials;
        }

        // Handle instructions for manual gateways
        if ($gateway->isManual()) {
            $data['instructions'] = $request->payment_instructions;
        }

        // Handle mode
        if ($gateway->mode && $request->filled('gateway_mode')) {
            $data['mode'] = $request->gateway_mode;
        }

        return $data;
    }

    /**
     * Validate business rules for gateway update.
     * Returns error message if validation fails, null if successful.
     */
    private function validateBusinessRules(array $updateData, PaymentGateway $gateway): ?string
    {
        // Validate charge currency is different from default
        if (!empty($updateData['charge_currency']) && $updateData['charge_currency'] === defaultCurrency()->code) {
            return translate('Charge currency must be different from default currency');
        }

        // Validate credentials for API gateways being activated
        if (!$gateway->isManual() && $updateData['is_active'] === true) {
            $credentialError = $this->validateCredentials($updateData, $gateway);
            if ($credentialError) {
                return $credentialError;
            }
        }

        // Validate instructions for manual gateways being activated
        if ($gateway->isManual() && $updateData['is_active'] === true) {
            $instructionError = $this->validateInstructions($updateData, $gateway);
            if ($instructionError) {
                return $instructionError;
            }
        }

        return null;
    }

    /**
     * Validate gateway credentials.
     * Returns error message if validation fails, null if successful.
     */
    private function validateCredentials(array $updateData, PaymentGateway $gateway): ?string
    {
        if (!isset($updateData['credentials'])) {
            return null;
        }

        $credentials = $updateData['credentials'];
        $existingCredentials = (array) $gateway->credentials;

        foreach ($credentials as $key => $value) {
            // Check if credential key exists in gateway
            if (!array_key_exists($key, $existingCredentials)) {
                return translate('Invalid credential key: :key', ['key' => $key]);
            }

            // Validate credential value is not empty when activating
            if ($updateData['is_active'] === true && empty($value)) {
                $readableKey = str_replace('_', ' ', ucwords($key));
                return translate(':key cannot be empty', ['key' => $readableKey]);
            }
        }

        return null;
    }

    /**
     * Validate payment instructions for manual gateways.
     * Returns error message if validation fails, null if successful.
     */
    private function validateInstructions(array $updateData, PaymentGateway $gateway): ?string
    {
        if ($gateway->isAccountBalance()) {
            return null;
        }

        if ($updateData['is_active'] === true && empty($updateData['instructions'])) {
            return translate('Payment instructions are required when activating manual gateway');
        }

        return null;
    }
}
