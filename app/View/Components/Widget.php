<?php

namespace App\View\Components;

use App\Widgets\WidgetManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\Component;

class Widget extends Component
{
    public string $name;
    public array $options;
    public mixed $context;

    /**
     * Create a new component instance.
     *
     * @param string $name The widget area name
     * @param array $options Additional options for rendering
     * @param mixed $context Context data (e.g., Product, User, Category) to share with widget views
     */
    public function __construct(string $name, array $options = [], mixed $context = null)
    {
        $this->name = $name;
        $this->options = $options;
        $this->context = $context;

        // Share the context with all views if provided
        if ($context) {
            // Determine the share key based on the context type
            $shareKey = $this->getContextShareKey($context);
            ViewFacade::share($shareKey, $context);
        }
    }

    /**
     * Determine the share key based on context type.
     */
    protected function getContextShareKey(mixed $context): string
    {
        if (is_object($context)) {
            $className = class_basename($context);
            return lcfirst($className);
        }

        return 'context';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|string
    {
        $widgetManager = app(WidgetManager::class);
        $content = $widgetManager->renderArea($this->name, $this->options);

        if (empty($content)) {
            return '';
        }

        return view('components.widget', [
            'content' => $content,
            'name' => $this->name,
        ]);
    }
}
