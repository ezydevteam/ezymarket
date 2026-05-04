<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Service for handling product queries, including filtering, sorting, and search.
 */
readonly class ProductQueryService
{
    /**
     * Apply search, filter, and sort parameters to a product query.
     *
     * @param Builder $query
     * @param Request $request
     * @param bool    $isSearchContext
     * @param string|null $searchTerm
     * @return Builder
     */
    public function getResultByParams(Builder $query, Request $request, bool $isSearchContext = false, ?string $searchTerm = null): Builder
    {
        if ($isSearchContext && !empty($searchTerm)) {
            $this->applySearchFilter($query, $searchTerm);
        }

        // Scope filters
        if (isPremiumAvailable() && $request->filled('premium')) {
            $query->premium();
        }

        if ($request->filled('free')) {
            $query->free();
        }

        if ($request->filled('on_sale')) {
            $query->onDiscount();
        }

        if ($request->filled('best_selling')) {
            $query->bestSelling();
        }

        if ($request->filled('trending')) {
            $query->trending();
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        if ($request->filled('stars')) {
            $query->where('avg_reviews', '>=', $request->input('stars'));
        }

        if ($request->filled('date')) {
            $this->applyDateFilter($query, (string) $request->input('date'));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $this->applyPriceFilter(
                $query,
                (string) $request->input('min_price'),
                (string) $request->input('max_price')
            );
        }

        // Sorting
        $sortBy = (string) ($request->input('sort_by') ?? ($isSearchContext && !empty($searchTerm) ? 'relevance' : 'newest_arrivals'));

        $this->applySorting($query, $sortBy, $isSearchContext, $searchTerm);

        return $query;
    }

    /**
     * Check if any filters (excluding sorting and query) are currently applied.
     */
    public function hasFilters(Request $request): bool
    {
        $filters = [
            'min_price', 'max_price', 'stars', 'date', 'free', 'premium',
            'on_sale', 'best_selling', 'trending', 'featured'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply full-text search filter to the products query.
     */
    private function applySearchFilter(Builder $query, string $searchTerm): void
    {
        $wildcard = '%' . $searchTerm . '%';
        $startsWith = $searchTerm . '%';

        $query->where(function ($q) use ($wildcard, $startsWith) {
            $q->where('name', 'like', $startsWith)
                ->orWhere(function ($subQ) use ($wildcard) {
                    $subQ->where('name', 'like', $wildcard)
                        ->orWhere('slug', 'like', $wildcard)
                        ->orWhere('description', 'like', $wildcard)
                        ->orWhere('options', 'like', $wildcard)
                        ->orWhere('demo_link', 'like', $wildcard)
                        ->orWhere('tags', 'like', $wildcard)
                        ->orWhereHas('category', fn($catQ) => $catQ->where('name', 'like', $wildcard))
                        ->orWhereHas('subCategory', fn($subCatQ) => $subCatQ->where('name', 'like', $wildcard));
                });
        });
    }

    /**
     * Apply a date range filter to the products query.
     */
    private function applyDateFilter(Builder $query, string $dateFilter): void
    {
        match ($dateFilter) {
            'this_month' => $query->whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ]),
            'last_month' => $query->whereBetween('created_at', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ]),
            'this_year'  => $query->whereYear('created_at', Carbon::now()->year),
            'last_year'  => $query->whereYear('created_at', Carbon::now()->subYear()->year),
            default      => null,
        };
    }

    /**
     * Apply min/max price filter with joined category buyer fees.
     */
    private function applyPriceFilter(Builder $query, ?string $minPrice, ?string $maxPrice): void
    {
        $query->join('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->select('products.*')
            ->where(function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice !== null && $minPrice !== '') {
                    $q->whereRaw('(products.regular_price + product_categories.regular_buyer_fee) >= ?', [$minPrice]);
                }
                if ($maxPrice !== null && $maxPrice !== '') {
                    $q->whereRaw('(products.regular_price + product_categories.regular_buyer_fee) <= ?', [$maxPrice]);
                }
            });
    }

    /**
     * Apply sorting to the products query.
     */
    private function applySorting(Builder $query, string $sortBy, bool $isSearch, ?string $searchTerm): void
    {
        match ($sortBy) {
            'best_selling'       => $query->orderByDesc('products.total_sales'),
            'recommended'        => $query->orderByDesc('products.avg_reviews')->orderByDesc('products.total_sales'),
            'newest_arrivals'    => $query->orderByDesc('products.created_at'),
            'price_high_to_low'  => $query->orderBy('products.is_free')->orderByDesc('products.regular_price'),
            'price_low_to_high'  => $query->orderByDesc('products.is_free')->orderBy('products.regular_price'),
            'rating_high_to_low' => $query->orderBy('products.is_free')->orderByDesc('products.avg_reviews'),
            'relevance'          => $this->applyRelevanceSorting($query, $isSearch, $searchTerm),
            default              => $this->applyRelevanceSorting($query, $isSearch, $searchTerm),
        };
    }

    /**
     * Apply relevance-based sorting (search context) or fallback to newest.
     */
    private function applyRelevanceSorting(Builder $query, bool $isSearch, ?string $searchTerm): void
    {
        if ($isSearch && !empty($searchTerm)) {
            $startsWith = $searchTerm . '%';
            $anywhere = '%' . $searchTerm . '%';

            $query->orderByRaw("
                CASE
                    WHEN products.name LIKE ? THEN 1
                    WHEN products.name LIKE ? THEN 2
                    WHEN products.description LIKE ? THEN 3
                    ELSE 4
                END", [$startsWith, $anywhere, $anywhere]
            );
        } else {
            $query->orderByDesc('products.created_at');
        }
    }
}
