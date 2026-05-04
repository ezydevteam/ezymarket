<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Offcanvas extends Component
{
    public $id;
    public $title;
    public $placement;
    public $scroll;
    public $backdrop;
    public $icon;
    public $header;
    public $closeButton;
    public $bodyClass;
    public $focus;

    /**
     * Create a new component instance.
     *
     * @param string $id Offcanvas unique ID
     * @param string $title Offcanvas title
     * @param string $placement Placement: start, end, top, bottom (default: end)
     * @param bool $scroll Allow body scrolling
     * @param bool $backdrop Show backdrop
     * @param string|null $icon Optional icon class for title
     * @param bool $header Show offcanvas header (default: true)
     * @param bool $closeButton Show close button in header
     * @param string $bodyClass Additional CSS classes for body
     * @param bool $focus Enable focus trap (default: true)
     * @return void
     */
    public function __construct(
        $id,
        $title,
        $placement = 'end',
        $scroll = false,
        $backdrop = true,
        $icon = null,
        $header = true,
        $closeButton = true,
        $bodyClass = '',
        $focus = true
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->placement = $placement;
        $this->scroll = $scroll;
        $this->backdrop = $backdrop;
        $this->icon = $icon;
        $this->header = $header;
        $this->closeButton = $closeButton;
        $this->bodyClass = $bodyClass;
        $this->focus = $focus;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.offcanvas');
    }
}
