<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $title;
    public $dialogClass;
    public $size;
    public $scrollable;
    public $centered;
    public $static;
    public $icon;
    public $header;
    public $closeButton;
    public $bodyClass;
    public $footerClass;
    public $autoOpen;
    public $contentOnly;
    public $contentClass;

    /**
     * Create a new component instance.
     *
     * @param string $id Modal unique ID
     * @param string $title Modal title
     * @param string $size Modal size: sm, lg, xl, default: null
     * @param bool $scrollable Enable scrollable modal body
     * @param bool $centered Center modal vertically
     * @param bool $static Prevent closing on backdrop click
     * @param string|null $icon Optional icon class for title
     * @param bool $header Show modal header (default: true)
     * @param bool $closeButton Show close button in header
     * @param string $bodyClass Additional CSS classes for modal body
     * @param string $footerClass Additional CSS classes for footer
     * @param string|bool|null $autoOpen Auto open modal based on URL param
     * @param bool $contentOnly Render only the internal modal-content (for AJAX)
     * @return void
     */
    public function __construct(
        $id = '',
        $title = '',
        $size = null,
        $scrollable = false,
        $centered = true,
        $static = false,
        $icon = null,
        $header = true,
        $closeButton = true,
        $bodyClass = '',
        $footerClass = '',
        $autoOpen = null,
        $contentOnly = false,
        $contentClass = ''
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->size = $size;
        $this->scrollable = $scrollable;
        $this->centered = $centered;
        $this->static = $static;
        $this->icon = $icon;
        $this->header = $header;
        $this->closeButton = $closeButton;
        $this->bodyClass = $bodyClass;
        $this->footerClass = $footerClass;
        $this->autoOpen = $autoOpen;
        $this->contentOnly = $contentOnly;
        $this->contentClass = $contentClass;

        // Build dialog classes
        $classes = [];
        if ($this->centered) {
            $classes[] = 'modal-dialog-centered';
        }
        if ($this->scrollable) {
            $classes[] = 'modal-dialog-scrollable';
        }
        if ($this->size) {
            $classes[] = 'modal-' . $this->size;
        }
        $this->dialogClass = implode(' ', $classes);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal');
    }
}
