<?php

namespace App\Livewire;

use App\Traits\LivewireToastr;
use Exception;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class NewsletterPopup extends Component
{
    use LivewireToastr;

    public $email;

    #[On('newsletterRefresh')]
    public function refresh()
    {
        // Livewire v3 will automatically refresh the component
    }

    public function remindLater()
    {
        Cookie::queue('nl_remind', true, (@settings('newsletter')->popup_reminder_time * 60));
        $this->dispatch('close-modal', id: 'newsletterModal');
    }

    public function subscribe()
    {
        $validator = Validator::make(['email' => $this->email], [
            'email' => ['required', 'string', 'email', 'indisposable'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return $this->toastr('error', $error);
            }
        }

        try {
            registerForNewsletter($this->email);
            Cookie::queue(Cookie::forever('nl_subscribed', true));

            $this->email = '';
            $this->dispatch('close-modal', id: 'newsletterModal');
            $this->dispatch('newsletterRefresh');

            return $this->toastr('success', translate('You have successfully subscribed'));
        } catch (Exception $e) {
            return $this->toastr('error', $e->getMessage());
        }
    }

    public function render()
    {
        return theme_view('livewire.newsletter-popup');
    }
}


















