<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Exception Handler for EasyMarket
 *
 * Centralizes exception handling for the entire application.
 * Provides custom responses for different exception types and request contexts.
 *
 * Features:
 * - Custom authentication redirect with login modal trigger
 * - JSON responses for API/AJAX requests
 * - Graceful error pages for web requests
 * - Enhanced error logging with context
 * - Rate limiting feedback
 * - Model not found handling
 *
 * Security:
 * - Sensitive input fields never flashed in session
 * - Detailed errors only in development mode
 * - Safe error messages for production
 *
 * @see https://laravel.com/docs/11.x/errors
 */
class Handler extends ExceptionHandler
{
    /**
     * Exception types that should not be reported to logs
     *
     * These exceptions are expected and don't need logging:
     * - Authentication failures (user not logged in)
     * - Authorization failures (user lacks permission)
     * - Validation errors (user input errors)
     * - Model not found (404 errors)
     * - Throttle exceptions (rate limiting)
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        ThrottleRequestsException::class,
    ];

    /**
     * Sensitive input fields that should never be flashed to session
     *
     * These fields are excluded from validation error messages for security:
     * - Prevents password exposure in session
     * - Protects sensitive credentials
     * - Complies with security best practices
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'password_new',
        'password_old',
        'token',
        'secret',
        'api_key',
        'api_secret',
        'card_number',
        'cvv',
        'pin',
    ];

    /**
     * Register exception handling callbacks
     *
     * Define custom reporting and rendering logic for specific exception types.
     * This method is called once during application bootstrap.
     *
     * @return void
     */
    public function register(): void
    {
        // Report all exceptions with enhanced context
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry') && $this->shouldReport($e)) {
                app('sentry')->captureException($e);
            }
        });

        // Custom rendering for specific exceptions
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'error' => 'Not Found',
                ], 404);
            }
        });

        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please slow down.',
                    'error' => 'Rate Limit Exceeded',
                    'retry_after' => $e->getHeaders()['Retry-After'] ?? null,
                ], 429);
            }
        });
    }

    /**
     * Render an exception into an HTTP response
     *
     * This is the main exception rendering method called for all exceptions.
     * Provides custom handling for different exception types and request contexts.
     *
     * @param Request $request The HTTP request
     * @param Throwable $exception The exception to render
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception): Response
    {
        // Handle authentication exceptions with custom redirect
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        // Handle model not found exceptions
        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFound($request, $exception);
        }

        // Handle HTTP exceptions (404, 403, etc.)
        if ($exception instanceof HttpException) {
            return $this->handleHttpException($request, $exception);
        }

        // Use Laravel's default rendering for all other exceptions
        return parent::render($request, $exception);
    }

    /**
     * Convert authentication exception into response
     *
     * Handles unauthenticated users trying to access protected routes.
     * Provides different responses for web vs API requests.
     *
     * For web requests:
     * - Redirects to intended URL or homepage
     * - Flashes 'openLoginModal' to trigger login modal
     *
     * For API/AJAX requests:
     * - Returns 401 JSON response
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        // API/AJAX requests get JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'message' => translate('Unauthenticated. Please login to continue.'),
                'error' => 'Unauthenticated',
                'login_required' => true,
            ], 401);
        }

        // Web requests redirect with login modal trigger
        $redirectTo = $exception->redirectTo() ?? '/';

        return redirect()->guest(url($redirectTo))
            ->with('openLoginModal', true)
            ->with('intended', $request->fullUrl());
    }

    /**
     * Handle model not found exceptions
     *
     * Provides user-friendly responses when database records aren't found.
     *
     * @param Request $request
     * @param ModelNotFoundException $exception
     * @return Response
     */
    protected function handleModelNotFound(Request $request, ModelNotFoundException $exception): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => translate('The requested resource was not found.'),
                'error' => 'Not Found',
            ], 404);
        }

        // Web requests show 404 page
        return response()->view('errors.404', [
            'exception' => $exception,
        ], 404);
    }

    /**
     * Handle HTTP exceptions (404, 403, 500, etc.)
     *
     * Provides appropriate responses for standard HTTP error codes.
     *
     * @param Request $request
     * @param HttpException $exception
     * @return Response
     */
    protected function handleHttpException(Request $request, HttpException $exception): Response
    {
        $statusCode = $exception->getStatusCode();
        $message = $exception->getMessage();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message ?: $this->getHttpErrorMessage($statusCode),
                'error' => $this->getHttpErrorTitle($statusCode),
                'status' => $statusCode,
            ], $statusCode);
        }

        // Check if custom error view exists
        $view = "errors.{$statusCode}";
        if (view()->exists($view)) {
            return response()->view($view, [
                'exception' => $exception,
            ], $statusCode);
        }

        // Fallback to Laravel's default handling
        return parent::render($request, $exception);
    }

    /**
     * Get user-friendly error message for HTTP status code
     *
     * @param int $statusCode HTTP status code
     * @return string Error message
     */
    private function getHttpErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => translate('Bad request. Please check your input.'),
            401 => translate('Unauthenticated. Please login to continue.'),
            403 => translate('You do not have permission to access this resource.'),
            404 => translate('The requested resource was not found.'),
            405 => translate('Method not allowed for this endpoint.'),
            408 => translate('Request timeout. Please try again.'),
            410 => translate('This resource is no longer available.'),
            419 => translate('Your session has expired. Please refresh the page.'),
            422 => translate('Validation failed. Please check your input.'),
            429 => translate('Too many requests. Please slow down.'),
            500 => translate('Internal server error. Please try again later.'),
            502 => translate('Bad gateway. The server is temporarily unavailable.'),
            503 => translate('Service unavailable. Please try again later.'),
            504 => translate('Gateway timeout. The server took too long to respond.'),
            default => translate('An error occurred. Please try again.'),
        };
    }

    /**
     * Get error title for HTTP status code
     *
     * @param int $statusCode HTTP status code
     * @return string Error title
     */
    private function getHttpErrorTitle(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthenticated',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            408 => 'Request Timeout',
            410 => 'Gone',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            default => 'Error',
        };
    }

    /**
     * Report or log an exception
     *
     * Adds custom context to exception logs for better debugging.
     *
     * @param Throwable $e
     * @return void
     * @throws Throwable
     */
    public function report(Throwable $e): void
    {
        // Add extra context for debugging
        if ($this->shouldReport($e)) {
            Log::error($e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_id' => auth()->id(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        parent::report($e);
    }
}

















