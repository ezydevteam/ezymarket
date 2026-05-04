<?php

namespace App\Services\Statistics\DTOs;

/**
 * GeoDataConfig
 *
 * Data Transfer Object for geographical data configuration.
 * Provides type-safe configuration for geo statistics generation.
 */
class GeoDataConfig
{
    public string $locationField;
    public string $aggregation;
    public string $aggregateField;
    public ?int $limit;
    public string $orderBy;
    public string $orderDirection;
    public bool $withPercentages;
    public ?string $countryCodeField;

    /**
     * Create a new GeoDataConfig instance
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->locationField = $config['locationField'] ?? 'country';
        $this->aggregation = $config['aggregation'] ?? 'count';
        $this->aggregateField = $config['aggregateField'] ?? '*';
        $this->limit = $config['limit'] ?? null;
        $this->orderBy = $config['orderBy'] ?? 'value';
        $this->orderDirection = $config['orderDirection'] ?? 'desc';
        $this->withPercentages = $config['withPercentages'] ?? true;
        $this->countryCodeField = $config['countryCodeField'] ?? null;
    }

    /**
     * Create configuration for country-based statistics
     *
     * @param string $locationField
     * @param string $aggregation
     * @param string $aggregateField
     * @param int|null $limit
     * @return self
     */
    public static function byCountry(
        string $locationField = 'country',
        string $aggregation = 'count',
        string $aggregateField = '*',
        ?int $limit = null
    ): self {
        return new self([
            'locationField' => $locationField,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
            'limit' => $limit,
        ]);
    }

    /**
     * Create configuration for top locations
     *
     * @param string $locationField
     * @param int $limit
     * @param string $aggregation
     * @param string $aggregateField
     * @return self
     */
    public static function topLocations(
        string $locationField = 'country',
        int $limit = 10,
        string $aggregation = 'count',
        string $aggregateField = '*'
    ): self {
        return new self([
            'locationField' => $locationField,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
            'limit' => $limit,
            'orderBy' => 'value',
            'orderDirection' => 'desc',
        ]);
    }

    /**
     * Create configuration for distribution map
     *
     * @param string $locationField
     * @param string $aggregation
     * @param string $aggregateField
     * @return self
     */
    public static function distributionMap(
        string $locationField = 'country',
        string $aggregation = 'count',
        string $aggregateField = '*'
    ): self {
        return new self([
            'locationField' => $locationField,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
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
        if (empty($this->locationField)) {
            throw new \InvalidArgumentException('Location field is required for geo data configuration');
        }

        $validAggregations = ['count', 'sum', 'avg', 'average', 'min', 'minimum', 'max', 'maximum'];
        if (!in_array($this->aggregation, $validAggregations)) {
            throw new \InvalidArgumentException(
                "Invalid aggregation type: {$this->aggregation}. Must be one of: " . implode(', ', $validAggregations)
            );
        }

        if ($this->aggregation !== 'count' && empty($this->aggregateField)) {
            throw new \InvalidArgumentException('Aggregate field is required for non-count aggregations');
        }

        if ($this->limit !== null && $this->limit < 1) {
            throw new \InvalidArgumentException('Limit must be a positive integer');
        }

        $validOrderDirections = ['asc', 'desc'];
        if (!in_array($this->orderDirection, $validOrderDirections)) {
            throw new \InvalidArgumentException(
                "Invalid order direction: {$this->orderDirection}. Must be 'asc' or 'desc'"
            );
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
            'locationField' => $this->locationField,
            'aggregation' => $this->aggregation,
            'aggregateField' => $this->aggregateField,
            'limit' => $this->limit,
            'orderBy' => $this->orderBy,
            'orderDirection' => $this->orderDirection,
            'withPercentages' => $this->withPercentages,
            'countryCodeField' => $this->countryCodeField,
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
}
