<?php

namespace App\View\Components;

use App\Models\SocialAuth;
use Illuminate\View\Component;

class SocialAuthButtons extends Component
{
    public function render()
    {
        $socialAuths = SocialAuth::active()->sorted()->get();
        return theme_view('components.social-auth-buttons', ['socialAuths' => $socialAuths]);
    }
}
