<?php

namespace App\View\Components;

use App\Methods\CaptchaValidator;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class Captcha extends Component
{
    public function render()
    {
        $captchaProvider = app(CaptchaValidator::class)->getDefaultCaptchaProvider();

        if ($captchaProvider) {
            $class = Str::studly($captchaProvider->alias) . 'Service';
            $fullClassName = "App\\Services\\Captcha\\{$class}";
            $service = new $fullClassName();

            $captcha = '<div class="captcha-wrapper mb-4">' . $service->render(getLocale()) . '</div>';

            return view('components.captcha', [
                'captcha' => $captcha,
            ]);
        }
        // It's good practice to return something even if $captchaProvider is null
        return '';
    }
}


















