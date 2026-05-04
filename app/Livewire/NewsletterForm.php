<?php

namespace App\Livewire;

use App\Traits\LivewireToastr;
use Exception;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\Attributes\On;

class NewsletterForm extends Component
{
    use LivewireToastr;

    public $email;
    public $name; // Add name property

    // Configurable options
    public $heading;
    public $subHeading;
    public $style = 'footer';
    public $placeholder = 'Enter your email';
    public $showName = false;
    public $namePlaceholder = 'Your Name';
    public $buttonText = 'Subscribe';
    public $buttonDisplay = 'text_only'; // text_only, icon_only, both
    public $buttonIcon = 'bi-send';
    public $buttonStyle = 'primary';

    public function mount(
        $heading = null,
        $subHeading = null,
        $style = 'footer',
        $placeholder = null,
        $showName = false,
        $namePlaceholder = null,
        $buttonText = null,
        $buttonDisplay = 'text_only',
        $buttonIcon = 'bi-send',
        $buttonStyle = 'primary'
    ) {
        $this->heading = $heading;
        $this->subHeading = $subHeading;
        $this->style = $style;
        $this->placeholder = $placeholder ?: translate('Enter your email');
        $this->showName = $showName;
        $this->namePlaceholder = $namePlaceholder ?: translate('Your Name');
        $this->buttonText = $buttonText ?: translate('Subscribe');
        $this->buttonDisplay = $buttonDisplay;
        $this->buttonIcon = $buttonIcon;
        $this->buttonStyle = $buttonStyle;

        // Pre-fill email/name if user is logged in
        if (authUser()) {
            $this->email = authUser()->email;
            $this->name = authUser()->name;
        }
    }

    #[On('newsletterRefresh')]
    public function refresh()
    {
        // Livewire v3 will automatically refresh the component
    }

    public function subscribe()
    {
        $rules = [
            'email' => ['required', 'string', 'email', 'indisposable'],
        ];

        if ($this->showName) {
            $rules['name'] = ['nullable', 'string', 'max:255'];
        }

        $validator = Validator::make(
            ['email' => $this->email, 'name' => $this->name],
            $rules
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                return $this->toastr('error', $error);
            }
        }

        // TODO: Update subscription logic to include name if present
        // Subscription::subscribe($this->email, $this->name);


        try {
            registerForNewsletter($this->email);
            Cookie::queue(Cookie::forever('nl_subscribed', true));

            $this->email = '';
            $this->dispatch('newsletterRefresh');

            return $this->toastr('success', translate('You have successfully subscribed'));
        } catch (Exception $e) {
            return $this->toastr('error', $e->getMessage());
        }
    }

    public function render()
    {
        return theme_view('livewire.newsletter-form');
    }
}
