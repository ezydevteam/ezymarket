<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Validator as ValidatorFacade;

trait HandlesValidation
{
    /**
     * Validate request and return validator instance.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator
     */
    protected function validateRequest(Request $request, array $rules, array $messages = [], array $attributes = []): Validator
    {
        return ValidatorFacade::make($request->only(array_keys($rules)), $rules, $messages, $attributes);
    }

    /**
     * Validate request and return validator instance.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator
     */
    protected function validateAllRequest(Request $request, array $rules, array $messages = [], array $attributes = []): Validator
    {
        return ValidatorFacade::make($request->all(), $rules, $messages, $attributes);
    }

    /**
     * Validate request and handle errors automatically with input.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator|RedirectResponse
     */
    protected function validateRequestWithInput(Request $request, array $rules, array $messages = [], array $attributes = [])
    {
        $validator = $this->validateRequest($request, $rules, $messages, $attributes);

        if ($validator->fails()) {
            return $this->handleValidationErrorsWithInput($validator);
        }

        return $validator;
    }

    /**
     * Validate request and handle errors automatically without input.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator|RedirectResponse
     */
    protected function validateRequestWithoutInput(Request $request, array $rules, array $messages = [], array $attributes = [])
    {
        $validator = $this->validateRequest($request, $rules, $messages, $attributes);

        if ($validator->fails()) {
            return $this->handleValidationErrors($validator);
        }

        return $validator;
    }

    /**
     * Validate request and handle errors for AJAX.
     *
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     * @return Validator|JsonResponse
     */
    protected function validateRequestJson(Request $request, array $rules, array $messages = [], array $attributes = [])
    {
        $validator = $this->validateRequest($request, $rules, $messages, $attributes);

        if ($validator->fails()) {
            return $this->handleValidationErrorsJson($validator);
        }

        return $validator;
    }

    /**
     * Handle validation errors by displaying toastr messages.
     * Returns back without input or errors.
     *
     * @param Validator $validator
     * @return RedirectResponse
     */
    protected function handleValidationErrors(Validator $validator): RedirectResponse
    {
        foreach ($validator->errors()->all() as $error) {
            toastr()->error($error);
        }

        return back();
    }

    /**
     * Handle validation errors with input preservation.
     *
     * @param Validator $validator
     * @return RedirectResponse
     */
    protected function handleValidationErrorsWithInput(Validator $validator): RedirectResponse
    {
        foreach ($validator->errors()->all() as $error) {
            toastr()->error($error);
        }

        return back()->withInput();
    }

    /**
     * Handle validation errors with both input and errors bag.
     *
     * @param Validator $validator
     * @return RedirectResponse
     */
    protected function handleValidationErrorsWithInputAndErrors(Validator $validator): RedirectResponse
    {
        foreach ($validator->errors()->all() as $error) {
            toastr()->error($error);
        }

        return back()->withInput()->withErrors($validator);
    }

    /**
     * Handle validation errors and redirect to a specific route.
     *
     * @param Validator $validator
     * @param string $route
     * @param array $parameters
     * @return RedirectResponse
     */
    protected function handleValidationErrorsWithRedirect(Validator $validator, string $route, array $parameters = []): RedirectResponse
    {
        foreach ($validator->errors()->all() as $error) {
            toastr()->error($error);
        }

        return redirect()->route($route, $parameters)->withInput();
    }

    /**
     * Handle validation errors for AJAX requests (returns JSON).
     *
     * @param Validator $validator
     * @return JsonResponse
     */
    protected function handleValidationErrorsJson(Validator $validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }

    // ============================================
    // SUCCESS MESSAGE & REDIRECT METHODS
    // ============================================

    /**
     * Show success message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function successBack(string $message = 'Operation completed successfully'): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => translate($message)
            ]);
        }
        toastr()->success(translate($message));
        return back();
    }

    /**
     * Show success message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse|JsonResponse
     */
    protected function successRedirectBack(string $message = 'Operation completed successfully'): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => translate($message)
            ]);
        }
        toastr()->success(translate($message));
        return redirect()->back();
    }

    /**
     * Show success message and redirect to route.
     *
     * @param string $route
     * @param array|string|int|null $routeParameters
     * @param string $message
     * @param array $messageParameters
     * @return RedirectResponse|JsonResponse
     */
    protected function successRedirect(string $route, array|string|int|null $routeParameters = [], string $message = 'Operation completed successfully', array $messageParameters = []): RedirectResponse|JsonResponse
    {
        // Normalize parameters to array format
        if (is_null($routeParameters)) {
            $routeParameters = [];
        } elseif (!is_array($routeParameters)) {
            $routeParameters = [$routeParameters];
        }

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => translate($message, $messageParameters),
                'redirect' => route($route, $routeParameters)
            ]);
        }

        toastr()->success(translate($message, $messageParameters));
        return redirect()->route($route, $routeParameters);
    }

    /**
     * Show info message and redirect to route.
     *
     * @param string $route
     * @param array|string|int|null $routeParameters
     * @param string $message
     * @param array $messageParameters
     * @return RedirectResponse|JsonResponse
     */
    protected function infoRedirect(string $route, array|string|int|null $routeParameters = [], string $message = 'Operation completed', array $messageParameters = []): RedirectResponse
    {
        // Normalize parameters to array format
        if (is_null($routeParameters)) {
            $routeParameters = [];
        } elseif (!is_array($routeParameters)) {
            $routeParameters = [$routeParameters];
        }

        toastr()->info(translate($message, $messageParameters));
        return redirect()->route($route, $routeParameters);
    }

    /**
     * Show success message and redirect to URL.
     *
     * @param string $url
     * @param string $message
     * @return RedirectResponse
     */
    protected function successRedirectUrl(string $url, string $message = 'Operation completed successfully'): RedirectResponse
    {
        toastr()->success(translate($message));
        return redirect($url);
    }

    /**
     * Show success message with JSON response.
     *
     * @param string $message
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    protected function successJson(string $message = 'Operation completed successfully', array $data = [], int $status = 200, array $parameters = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => translate($message, $parameters),
            'data' => $data,
        ], $status);
    }

    /**
     * Created success message and redirect to route.
     *
     * @param string $route
     * @param array|string|int|null $parameters
     * @param string $message
     * @return RedirectResponse
     */
    protected function createdRedirect(string $route, array|string|int|null $parameters = [], string $message = 'Created Successfully'): RedirectResponse
    {
        // Normalize parameters to array format
        if (is_null($parameters)) {
            $parameters = [];
        } elseif (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        toastr()->success(translate($message));
        return redirect()->route($route, $parameters);
    }

    /**
     * Updated success message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function updatedBack(string $message = 'Updated Successfully'): RedirectResponse|JsonResponse
    {
        return $this->successBack($message);
    }

    /**
     * Deleted success message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse|JsonResponse
     */
    protected function deletedBack(string $message = 'Deleted Successfully'): RedirectResponse|JsonResponse
    {
        return $this->successBack($message);
    }

    /**
     * Deleted success message and redirect to route.
     *
     * @param string $route
     * @param array $parameters
     * @param string $message
     * @return RedirectResponse
     */
    protected function deletedRedirect(string $route, array|string|int|null $parameters = [], string $message = 'Deleted Successfully'): RedirectResponse
    {
        toastr()->success(translate($message));
        return redirect()->route($route, $parameters);
    }

    // ============================================
    // ERROR MESSAGE & REDIRECT METHODS
    // ============================================

    /**
     * Show error message and redirect back.
     *
     * @param string $message
     * @param array $parameters
     * @return RedirectResponse
     */
    protected function errorBack(string $message = 'Operation failed', array $parameters = []): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => translate($message, $parameters)
            ], 400);
        }
        toastr()->error(translate($message, $parameters));
        return back();
    }

    /**
     * Show error message with input and redirect back.
     *
     * @param string $message
     * @param array $parameters
     * @return RedirectResponse
     */
    protected function errorBackWithInput(string $message = 'Operation failed', array $parameters = []): RedirectResponse
    {
        toastr()->error(translate($message, $parameters));
        return back()->withInput();
    }

    /**
     * Show error message and redirect to route.
     *
     * @param string $route
     * @param array|string|int|null $parameters
     * @param string $message
     * @param array $messageParameters
     * @return RedirectResponse
     */
    protected function errorRedirect(string $route, array|string|int|null $parameters = [], string $message = 'Operation failed', array $messageParameters = []): RedirectResponse
    {
        // Normalize parameters to array format
        if (is_null($parameters)) {
            $parameters = [];
        } elseif (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        toastr()->error(translate($message, $messageParameters));
        return redirect()->route($route, $parameters);
    }

    /**
     * Show error message with JSON response.
     *
     * @param string $message
     * @param array $errors
     * @param int $status
     * @return JsonResponse
     */
    protected function errorJson(string $message = 'Operation failed', array $errors = [], int $status = 400, array $parameters = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => translate($message, $parameters),
            'errors' => $errors,
        ], $status);
    }

    // ============================================
    // WARNING & INFO MESSAGE METHODS
    // ============================================

    /**
     * Show warning message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function warningBack(string $message): RedirectResponse
    {
        toastr()->warning(translate($message));
        return back();
    }

    /**
     * Show info message and redirect back.
     *
     * @param string $message
     * @return RedirectResponse
     */
    protected function infoBack(string $message): RedirectResponse
    {
        toastr()->info(translate($message));
        return back();
    }

    // ============================================
    // BULK ACTION METHODS
    // ============================================

    /**
     * Handle bulk actions with validation and callback execution.
     *
     * @param Request $request
     * @param callable $callback - Callback function that receives the collection of items
     * @param string|null $modelClass - Model class name for validation (optional)
     * @param string $successMessage - Success message with :count placeholder
     * @param string $errorMessage - Error message for failures
     * @return JsonResponse
     */
    protected function handleBulkAction(
        Request $request,
        callable $callback,
        ?string $modelClass = null,
        string $successMessage = ':count item(s) processed successfully',
        string $errorMessage = 'An error occurred while processing items'
    ): JsonResponse {
        // Build validation rules
        $rules = [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'numeric'],
        ];

        // Add exists validation if model class is provided
        if ($modelClass) {
            $tableName = (new $modelClass)->getTable();
            $rules['ids.*'][] = "exists:{$tableName},id";
        }

        // Validate request
        $validator = ValidatorFacade::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Execute the callback with the IDs
            $result = $callback($request->ids, $request);

            // Calculate total attempted
            $total = count($request->ids);

            // Handle different return types
            if (is_array($result)) {
                // Array result with custom message, count or data
                $count = $result['count'] ?? $total;
                $message = isset($result['message'])
                    ? translate($result['message'])
                    : translate($successMessage, ['count' => $count, 'total' => $total]);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'count' => $count,
                    'total' => $total,
                    'data' => $result['data'] ?? []
                ]);
            } elseif (is_numeric($result)) {
                // Numeric result (count)
                return response()->json([
                    'success' => true,
                    'message' => translate($successMessage, ['count' => $result, 'total' => $total]),
                    'count' => (int) $result,
                    'total' => $total,
                ]);
            } else {
                // Boolean or other result - fallback to total if true, else 0
                $count = ($result === false) ? 0 : $total;
                return response()->json([
                    'success' => true,
                    'message' => translate($successMessage, ['count' => $count, 'total' => $total]),
                    'count' => $count,
                    'total' => $total,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage() ?: translate($errorMessage)
            ], 500);
        }
    }
}
