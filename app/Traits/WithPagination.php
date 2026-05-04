<?php

namespace App\Traits;

use Illuminate\Pagination\{Cursor, CursorPaginator, Paginator};

/**
 * WithPagination trait for Livewire components
 *
 * Provides pagination functionality with support for:
 * - Multiple paginators on the same page
 * - Cursor-based pagination
 * - Custom pagination views
 * - Page navigation methods
 * - Query string synchronization
 */
trait WithPagination
{
    /**
     * Current page number
     */
    public int $page = 1;

    /**
     * Array of paginator instances
     */
    public array $paginators = [];

    /**
     * Track number of rendered paginators
     */
    protected array $numberOfPaginatorsRendered = [];

    /**
     * Define query string parameters for pagination
     */
    public function queryStringWithPagination(): array
    {
        foreach ($this->paginators as $key => $value) {
            $this->$key = $value;
        }

        return array_fill_keys(array_keys($this->paginators), ['except' => 1]);
    }

    /**
     * Initialize pagination on component mount
     */
    public function initializeWithPagination(): void
    {
        // Initialize paginator properties
        foreach ($this->paginators as $key => $value) {
            $this->$key = $value;
        }

        // Resolve current page
        $this->page = $this->resolvePage();
        $this->paginators['page'] = $this->page;

        // Set up cursor pagination resolver if available
        if (class_exists(CursorPaginator::class)) {
            CursorPaginator::currentCursorResolver(function ($paginatorName) {
                if (!isset($this->paginators[$paginatorName])) {
                    $this->paginators[$paginatorName] = request()->query($paginatorName, '');
                }
                return Cursor::fromEncoded($this->paginators[$paginatorName]);
            });
        }

        // Set up page pagination resolver
        Paginator::currentPageResolver(function ($paginatorName) {
            if (!isset($this->paginators[$paginatorName])) {
                $this->paginators[$paginatorName] = request()->query($paginatorName, 1);
            }

            return (int) $this->paginators[$paginatorName];
        });

        // Set default pagination view
        Paginator::defaultView($this->paginationView());
    }

    /**
     * Get the pagination view path
     */
    public function paginationView(): string
    {
        $themePrefix = themeManager()->getActiveThemeViewPrefix();
        return "{$themePrefix}.livewire.pagination";
    }

    /**
     * Navigate to previous page
     */
    public function previousPage(string $paginatorName = 'page'): void
    {
        $currentPage = $this->paginators[$paginatorName];
        $this->setPage(max($currentPage - 1, 1), $paginatorName);
    }

    /**
     * Navigate to next page
     */
    public function nextPage(string $paginatorName = 'page'): void
    {
        $currentPage = $this->paginators[$paginatorName];
        $this->setPage($currentPage + 1, $paginatorName);
    }

    /**
     * Navigate to specific page
     */
    public function gotoPage(int $page, string $paginatorName = 'page'): void
    {
        $this->setPage($page, $paginatorName);
    }

    /**
     * Reset pagination to first page
     */
    public function resetPage(string $paginatorName = 'page'): void
    {
        $this->setPage(1, $paginatorName);
    }

    /**
     * Set the current page with lifecycle hooks
     */
    public function setPage(int|string $page, string $paginatorName = 'page'): void
    {
        // Normalize page number
        if (is_numeric($page)) {
            $page = (int) $page;
            $page = $page <= 0 ? 1 : $page;
        }

        // Define lifecycle hook method names
        $beforePaginatorHook = 'updatingPaginators';
        $afterPaginatorHook = 'updatedPaginators';
        $beforePageHook = 'updating' . ucfirst($paginatorName);
        $afterPageHook = 'updated' . ucfirst($paginatorName);

        // Call before hooks
        $this->callHookIfExists($beforePaginatorHook, $page, $paginatorName);
        $this->callHookIfExists($beforePageHook, $page, null);

        // Update page values
        $this->paginators[$paginatorName] = $page;
        $this->$paginatorName = $page;

        // Call after hooks
        $this->callHookIfExists($afterPaginatorHook, $page, $paginatorName);
        $this->callHookIfExists($afterPageHook, $page, null);

        // Dispatch deselect event
        $this->dispatch('deselectall');
    }

    /**
     * Resolve the current page from request
     */
    public function resolvePage(): int
    {
        $paginatorName = 'page';

        // Try to get custom paginator name from query string config
        if (method_exists($this, 'getQueryString')) {
            $queryStringConfig = $this->getQueryString();
            $paginatorName = data_get($queryStringConfig, 'page.as', 'page');
        } elseif (property_exists($this, 'queryString') && is_array($this->queryString)) {
            $paginatorName = data_get($this->queryString, 'page.as', 'page');
        }

        return (int) request()->query($paginatorName, $this->page);
    }

    /**
     * Call lifecycle hook method if it exists
     */
    protected function callHookIfExists(string $method, mixed $page, ?string $paginatorName): void
    {
        if (method_exists($this, $method)) {
            $this->{$method}($page, $paginatorName);
        }
    }
}


















