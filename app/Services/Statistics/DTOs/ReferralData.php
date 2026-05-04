<?php

namespace App\Services\Statistics\DTOs;

use Illuminate\Support\Collection;

/**
 * ReferralData
 *
 * Data Transfer Object for referral/traffic source statistics response.
 * Standardizes referral data output format.
 */
class ReferralData
{
    public Collection $data;
    public ?float $total;
    public ?array $percentages;
    public ?array $categories;
    public ?string $referrerField;

    /**
     * Create a new ReferralData instance
     *
     * @param Collection $data
     * @param float|null $total
     * @param array|null $percentages
     * @param array|null $categories
     * @param string|null $referrerField
     */
    public function __construct(
        Collection $data,
        ?float $total = null,
        ?array $percentages = null,
        ?array $categories = null,
        ?string $referrerField = null
    ) {
        $this->data = $data;
        $this->total = $total;
        $this->percentages = $percentages;
        $this->categories = $categories;
        $this->referrerField = $referrerField;
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            collect($data['data'] ?? []),
            $data['total'] ?? null,
            $data['percentages'] ?? null,
            $data['categories'] ?? null,
            $data['referrerField'] ?? null
        );
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'data' => $this->data->toArray(),
        ];

        if ($this->total !== null) {
            $array['total'] = $this->total;
        }

        if ($this->percentages !== null) {
            $array['percentages'] = $this->percentages;
        }

        if ($this->categories !== null) {
            $array['categories'] = $this->categories;
        }

        if ($this->referrerField !== null) {
            $array['referrerField'] = $this->referrerField;
        }

        return $array;
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Get number of referrers
     *
     * @return int
     */
    public function count(): int
    {
        return $this->data->count();
    }

    /**
     * Get top N referrers
     *
     * @param int $limit
     * @return Collection
     */
    public function top(int $limit): Collection
    {
        return $this->data->take($limit);
    }

    /**
     * Get referrers with count greater than threshold
     *
     * @param int $threshold
     * @return Collection
     */
    public function above(int $threshold): Collection
    {
        return $this->data->filter(fn($item) => $item['count'] > $threshold);
    }

    /**
     * Find referrer by name
     *
     * @param string $referrer
     * @return mixed
     */
    public function findByReferrer(string $referrer): mixed
    {
        return $this->data->firstWhere('referrer', $referrer);
    }

    /**
     * Get all referrers as array
     *
     * @return array
     */
    public function referrers(): array
    {
        return $this->data->pluck('referrer')->toArray();
    }

    /**
     * Get all counts as array
     *
     * @return array
     */
    public function counts(): array
    {
        return $this->data->pluck('count')->toArray();
    }

    /**
     * Calculate sum of all counts
     *
     * @return int
     */
    public function sum(): int
    {
        return $this->data->sum('count');
    }

    /**
     * Calculate average count
     *
     * @return float
     */
    public function average(): float
    {
        $count = $this->count();
        return $count > 0 ? $this->sum() / $count : 0;
    }

    /**
     * Get referrer with highest count
     *
     * @return mixed
     */
    public function highest(): mixed
    {
        return $this->data->sortByDesc('count')->first();
    }

    /**
     * Get referrer with lowest count
     *
     * @return mixed
     */
    public function lowest(): mixed
    {
        return $this->data->sortBy('count')->first();
    }

    /**
     * Get referrers by category
     *
     * @param string $category
     * @return Collection
     */
    public function byCategory(string $category): Collection
    {
        if (!isset($this->categories[$category])) {
            return collect([]);
        }

        return $this->data->filter(function ($item) use ($category) {
            return isset($item['category']) && $item['category'] === $category;
        });
    }

    /**
     * Get social media referrers
     *
     * @return Collection
     */
    public function social(): Collection
    {
        return $this->byCategory('social');
    }

    /**
     * Get search engine referrers
     *
     * @return Collection
     */
    public function searchEngines(): Collection
    {
        return $this->byCategory('search');
    }

    /**
     * Get email referrers
     *
     * @return Collection
     */
    public function email(): Collection
    {
        return $this->byCategory('email');
    }

    /**
     * Get direct traffic
     *
     * @return Collection
     */
    public function direct(): Collection
    {
        return $this->byCategory('direct');
    }

    /**
     * Get other/unknown referrers
     *
     * @return Collection
     */
    public function other(): Collection
    {
        return $this->byCategory('other');
    }

    /**
     * Get category distribution
     *
     * @return array
     */
    public function categoryDistribution(): array
    {
        if (!$this->categories) {
            return [];
        }

        $distribution = [];
        foreach (array_keys($this->categories) as $category) {
            $categoryData = $this->byCategory($category);
            $distribution[$category] = [
                'count' => $categoryData->sum('count'),
                'percentage' => $this->total > 0
                    ? round(($categoryData->sum('count') / $this->total) * 100, 1)
                    : 0,
            ];
        }

        return $distribution;
    }

    /**
     * Check if referrer exists
     *
     * @param string $referrer
     * @return bool
     */
    public function hasReferrer(string $referrer): bool
    {
        return $this->data->contains('referrer', $referrer);
    }

    /**
     * Filter by search term
     *
     * @param string $search
     * @return Collection
     */
    public function search(string $search): Collection
    {
        return $this->data->filter(function ($item) use ($search) {
            return stripos($item['referrer'], $search) !== false;
        });
    }
}
