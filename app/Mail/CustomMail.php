<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Custom Mail
 *
 * Generic mailable class for sending emails with custom subject and body.
 * Used for system-wide notifications, announcements, and custom messages.
 *
 * Features:
 * - Customizable subject line
 * - Markdown-formatted body content
 * - Queue support for async sending
 * - Serializable for job queue
 *
 * Usage:
 * ```php
 * Mail::to($user)->send(new CustomMail(
 *     subject: 'Welcome to EasyMarket',
 *     body: '# Hello! Thanks for joining us.'
 * ));
 * ```
 *
 * Template:
 * - View: resources/views/emails/default.blade.php
 * - Format: Markdown
 *
 * @package App\Mail
 */
class CustomMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance
     *
     * Uses PHP 8.3 constructor property promotion for cleaner code.
     *
     * @param string $subject Email subject line
     * @param string $body Email body content (supports Markdown)
     */
    public function __construct(
        public string $subject,
        public string $body
    ) {
    }

    /**
     * Build the message
     *
     * Constructs the email with subject and Markdown template.
     *
     * @return self The mailable instance
     */
    public function build(): self
    {
        return $this->subject($this->subject)
            ->markdown('emails.default');
    }
}



















