<?php

namespace App\Http\Controllers\Admin\Mail;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Traits\HandlesValidation;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Artisan, Mail};
use Illuminate\View\View;

/**
 * Mail Settings Controller
 *
 * Handles mail configuration management including SMTP settings,
 * environment variable updates, and mail server testing.
 */
class MailSettingsController extends Controller
{
    use HandlesValidation;

    /**
     * Display mail settings page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('admin.mail.settings');
    }

    /**
     * Update mail configuration settings.
     *
     * Updates both database settings and .env file, then clears config cache
     * to ensure changes take effect immediately.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = $this->validateRequestWithoutInput($request, [
            'mail.driver' => ['required_if:mail.status,on', 'in:smtp,sendmail'],
            'mail.host' => ['required_if:mail.status,on'],
            'mail.port' => ['required_if:mail.status,on'],
            'mail.username' => ['required_if:mail.status,on'],
            'mail.password' => ['required_if:mail.status,on'],
            'mail.encryption' => ['required_if:mail.status,on', 'in:ssl,tls'],
            'mail.from_email' => ['required_if:mail.status,on'],
            'mail.from_name' => ['required_if:mail.status,on'],
        ]);

        if ($validator instanceof RedirectResponse) {
            return $validator;
        }

        $data = $request->mail;
        $data['status'] = $request->has('mail.status') ? 1 : 0;

        $update = Settings::updateSettings('mail', $data);

        if (!$update) {
            return $this->errorBack('Update Failed');
        }

        // Sync mail settings to environment file
        $this->syncMailEnvironment($data);

        return $this->updatedBack();
    }

    /**
     * Test mail server configuration by sending a test email.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function test(Request $request): JsonResponse|RedirectResponse
    {
        $validator = $this->validateRequest($request, [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return $this->handleValidationErrorsJson($validator);
            }
            return $this->handleValidationErrorsWithInput($validator);
        }

        if (!@settings('mail')->status) {
            $message = 'Mail server is not enabled';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => translate($message)
                ], 400);
            }

            return $this->errorBackWithInput($message);
        }

        try {
            $this->sendTestMail($request->email);

            $message = 'Sent successfully';

            if ($request->expectsJson()) {
                return $this->successJson($message);
            }

            return $this->successBack($message);
        } catch (\Exception $e) {
            $message = 'Sending failed: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => translate($message)
                ], 500);
            }

            return $this->errorBackWithInput($message);
        }
    }
    /**
     * Sync mail configuration to environment file.
     *
     * Updates .env file with mail settings and clears config cache
     * to apply changes immediately.
     *
     * @param array $data Mail configuration data
     * @return void
     */
    private function syncMailEnvironment(array $data): void
    {
        setEnv('MAIL_MAILER', $data['driver']);
        setEnv('MAIL_HOST', $data['host']);
        setEnv('MAIL_PORT', (int) $data['port']);
        setEnv('MAIL_USERNAME', $data['username']);
        setEnv('MAIL_PASSWORD', $data['password'], true);
        setEnv('MAIL_ENCRYPTION', $data['encryption']);
        setEnv('MAIL_FROM_ADDRESS', $data['from_email']);
        setEnv('MAIL_FROM_NAME', $data['from_name'], true);

        // Clear config cache to apply changes immediately
        Artisan::call('config:clear');
    }

    /**
     * Send a test email to verify mail server configuration.
     *
     * @param string $email Recipient email address
     * @return void
     * @throws \Exception If mail sending fails
     */
    private function sendTestMail(string $email): void
    {
        Mail::raw('Hello dear, This is a test mail to ' . $email, function ($message) use ($email) {
            $message->to($email)
                ->subject('Test mail to ' . $email);
        });
    }
}
