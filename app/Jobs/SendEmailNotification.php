<?php

namespace App\Jobs;

use App\Mail\SendMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Base Email Notification Job
 *
 * Base class for sending email notifications with queue support.
 * Handles email validation, sending, and failure logging.
 *
 * Features:
 * - Email validation before sending
 * - Automatic retry on failure (3 attempts)
 * - Failed job logging
 * - Template-based email system
 *
 * Usage:
 * SendEmailNotification::dispatch($user, 'template_name', $data, 'event_name');
 *
 * @package App\Jobs
 */
class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The notifiable entity (user/admin)
     *
     * @var mixed
     */
    protected $notifiable;

    /**
     * Email template name
     *
     * @var string
     */
    protected string $template;

    /**
     * Template data array
     *
     * @var array
     */
    protected array $data;

    /**
     * Event name for logging
     *
     * @var string
     */
    protected string $event;

    /**
     * Target email address (optional override)
     *
     * @var string|null
     */
    protected ?string $targetEmail;

    /**
     * Create a new job instance
     *
     * @param string $template Email template name
     * @param array $data Template data
     * @param string $event Event name for logging
     * @param string|null $targetEmail Optional target email override
     */
    public function __construct($notifiable, string $template, array $data, string $event, ?string $targetEmail = null)
    {
        $this->notifiable = $notifiable;
        $this->template = $template;
        $this->data = $data;
        $this->event = $event;
        $this->targetEmail = $targetEmail;
    }

    /**
     * Execute the job
     *
     * Validates email and sends notification using the template system.
     *
     * @return void
     */
    public function handle(): void
    {
        $email = $this->targetEmail ?? $this->notifiable->email;

        // Validate email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Send email notification
        SendMail::send($email, $this->template, $this->data);
    }

    /**
     * Handle a job failure
     *
     * Logs failed email notifications for debugging.
     *
     * @param \Throwable $exception The exception that caused the failure
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send email notification', [
            'user_id' => $this->notifiable->id ?? null,
            'email' => $this->notifiable->email ?? null,
            'template' => $this->template,
            'event' => $this->event,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}


















