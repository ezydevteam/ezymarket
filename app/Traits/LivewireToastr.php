<?php

namespace App\Traits;

trait LivewireToastr
{
    public function toastr(string $type = 'success', string $message = '', array $options = [])
    {
        return $this->dispatch('alert',
            type: $type,
            message: $message,
            options: $options
        );
    }
}


















