<?php

namespace App\Services\Statistics\DTOs;

/**
 * ReferralConfig
 *
 * Data Transfer Object for referral/traffic source configuration.
 * Provides type-safe configuration for referral statistics generation.
 */
class ReferralConfig
{
    public string $referrerField;
    public ?int $limit;
    public string $orderBy;
    public string $orderDirection;
    public bool $withPercentages;
    public bool $categorize;
    public array $categories;

    /**
     * Create a new ReferralConfig instance
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->referrerField = $config['referrerField'] ?? 'referrer';
        $this->limit = $config['limit'] ?? null;
        $this->orderBy = $config['orderBy'] ?? 'count';
        $this->orderDirection = $config['orderDirection'] ?? 'desc';
        $this->withPercentages = $config['withPercentages'] ?? true;
        $this->categorize = $config['categorize'] ?? false;
        $this->categories = $config['categories'] ?? [
            'social' => ['facebook', 'twitter', 'instagram', 'linkedin', 'pinterest', 'reddit', 'tiktok'],
            'search' => ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu'],
            'email' => ['mail', 'email', 'gmail', 'outlook'],
            'direct' => ['direct', 'bookmark'],
        ];
    }

    /**
     * Create configuration for top referrers
     *
     * @param string $referrerField
     * @param int $limit
     * @return self
     */
    public static function topReferrers(
        string $referrerField = 'referrer',
        int $limit = 10
    ): self {
        return new self([
            'referrerField' => $referrerField,
            'limit' => $limit,
            'orderBy' => 'count',
            'orderDirection' => 'desc',
            'withPercentages' => true,
        ]);
    }

    /**
     * Create configuration for referrer distribution
     *
     * @param string $referrerField
     * @param int|null $limit
     * @return self
     */
    public static function distribution(
        string $referrerField = 'referrer',
        ?int $limit = null
    ): self {
        return new self([
            'referrerField' => $referrerField,
            'limit' => $limit,
            'withPercentages' => true,
        ]);
    }

    /**
     * Create configuration for traffic sources (categorized)
     *
     * @param string $referrerField
     * @return self
     */
    public static function trafficSources(
        string $referrerField = 'referrer'
    ): self {
        return new self([
            'referrerField' => $referrerField,
            'categorize' => true,
            'withPercentages' => true,
        ]);
    }

    /**
     * Validate the configuration
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate(): bool
    {
        if (empty($this->referrerField)) {
            throw new \InvalidArgumentException('Referrer field is required for referral configuration');
        }

        if ($this->limit !== null && $this->limit < 1) {
            throw new \InvalidArgumentException('Limit must be a positive integer');
        }

        $validOrderBy = ['count', 'referrer'];
        if (!in_array($this->orderBy, $validOrderBy)) {
            throw new \InvalidArgumentException(
                "Invalid order by: {$this->orderBy}. Must be 'count' or 'referrer'"
            );
        }

        $validOrderDirections = ['asc', 'desc'];
        if (!in_array($this->orderDirection, $validOrderDirections)) {
            throw new \InvalidArgumentException(
                "Invalid order direction: {$this->orderDirection}. Must be 'asc' or 'desc'"
            );
        }

        if ($this->categorize && empty($this->categories)) {
            throw new \InvalidArgumentException('Categories array cannot be empty when categorize is enabled');
        }

        return true;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'referrerField' => $this->referrerField,
            'limit' => $this->limit,
            'orderBy' => $this->orderBy,
            'orderDirection' => $this->orderDirection,
            'withPercentages' => $this->withPercentages,
            'categorize' => $this->categorize,
            'categories' => $this->categories,
        ];
    }

    /**
     * Set limit
     *
     * @param int $limit
     * @return self
     */
    public function withLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set ordering
     *
     * @param string $orderBy
     * @param string $orderDirection
     * @return self
     */
    public function orderBy(string $orderBy, string $orderDirection = 'desc'): self
    {
        $this->orderBy = $orderBy;
        $this->orderDirection = $orderDirection;
        return $this;
    }

    /**
     * Enable or disable percentages
     *
     * @param bool $enabled
     * @return self
     */
    public function withPercentages(bool $enabled = true): self
    {
        $this->withPercentages = $enabled;
        return $this;
    }

    /**
     * Enable or disable categorization
     *
     * @param bool $enabled
     * @return self
     */
    public function withCategorization(bool $enabled = true): self
    {
        $this->categorize = $enabled;
        return $this;
    }

    /**
     * Set custom categories
     *
     * @param array $categories
     * @return self
     */
    public function withCategories(array $categories): self
    {
        $this->categories = $categories;
        $this->categorize = true;
        return $this;
    }

    /**
     * Add a custom category
     *
     * @param string $name
     * @param array $keywords
     * @return self
     */
    public function addCategory(string $name, array $keywords): self
    {
        $this->categories[$name] = $keywords;
        return $this;
    }
}
