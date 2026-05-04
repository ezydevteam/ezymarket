<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The contact form data.
     *
     * @var array
     */
    public $data;

    /**
     * The absolute path to the attached image.
     *
     * @var string|null
     */
    public $imagePath;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param string|null $imagePath
     */
    public function __construct(array $data, ?string $imagePath = null)
    {
        $this->data = $data;
        $this->imagePath = $imagePath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->from(config('mail.from.address'), $this->data['name'])
            ->replyTo($this->data['email'])
            ->subject($this->data['subject'])
            ->html($this->getEmailBody());

        if ($this->imagePath && file_exists($this->imagePath)) {
            $mail->attach($this->imagePath, [
                'as' => 'attachment.' . pathinfo($this->imagePath, PATHINFO_EXTENSION),
            ]);
        }

        return $mail;
    }

    /**
     * Generate the HTML body for the email.
     *
     * @return string
     */
    protected function getEmailBody(): string
    {
        $body = "<p><strong>Name:</strong> {$this->data['name']}</p>";
        $body .= "<p><strong>Email:</strong> {$this->data['email']}</p>";
        $body .= "<p><strong>Subject:</strong> {$this->data['subject']}</p>";

        if (!empty($this->data['url'])) {
            $body .= "<p><strong>Product Link:</strong> <a href='{$this->data['url']}'>{$this->data['url']}</a></p>";
        }

        $body .= "<p><strong>Message:</strong><br>" . nl2br(e($this->data['message'])) . "</p>";

        return $body;
    }
}
