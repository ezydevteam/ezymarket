<?php

namespace App\View\Components;

use App\Models\Advertisement as AdvertisementModel;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Advertisement Component
 *
 * Displays advertisements by alias throughout the theme.
 */
class Advertisement extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $alias
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $code = null;
        $advertisement = AdvertisementModel::findActiveByAlias($this->alias);

        if ($advertisement) {
            $code = $advertisement->code;
        }

        return theme_view('components.advertisement', [
            'code' => $code,
        ]);
    }
}
