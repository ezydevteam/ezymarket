<?php

namespace App\Services\Statistics\DTOs;

use Illuminate\Support\Collection;

/**
 * GeoData
 *
 * Data Transfer Object for geographical statistics response.
 * Standardizes geo data output format.
 */
class GeoData
{
    public Collection $data;
    public ?float $total;
    public ?array $percentages;
    public ?string $locationField;

    /**
     * Create a new GeoData instance
     *
     * @param Collection $data
     * @param float|null $total
     * @param array|null $percentages
     * @param string|null $locationField
     */
    public function __construct(
        Collection $data,
        ?float $total = null,
        ?array $percentages = null,
        ?string $locationField = null
    ) {
        $this->data = $data;
        $this->total = $total;
        $this->percentages = $percentages;
        $this->locationField = $locationField;
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
            $data['locationField'] ?? null
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

        if ($this->locationField !== null) {
            $array['locationField'] = $this->locationField;
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
     * Get number of locations
     *
     * @return int
     */
    public function count(): int
    {
        return $this->data->count();
    }

    /**
     * Get top N locations
     *
     * @param int $limit
     * @return Collection
     */
    public function top(int $limit): Collection
    {
        return $this->data->take($limit);
    }

    /**
     * Get locations with value greater than threshold
     *
     * @param float $threshold
     * @return Collection
     */
    public function above(float $threshold): Collection
    {
        return $this->data->filter(fn($item) => $item['value'] > $threshold);
    }

    /**
     * Get locations by name
     *
     * @param string $location
     * @return mixed
     */
    public function findByLocation(string $location): mixed
    {
        return $this->data->firstWhere('location', $location);
    }

    /**
     * Get all locations as array
     *
     * @return array
     */
    public function locations(): array
    {
        return $this->data->pluck('location')->toArray();
    }

    /**
     * Get all values as array
     *
     * @return array
     */
    public function values(): array
    {
        return $this->data->pluck('value')->toArray();
    }

    /**
     * Calculate sum of all values
     *
     * @return float
     */
    public function sum(): float
    {
        return $this->data->sum('value');
    }

    /**
     * Calculate average value
     *
     * @return float
     */
    public function average(): float
    {
        $count = $this->count();
        return $count > 0 ? $this->sum() / $count : 0;
    }

    /**
     * Get location with highest value
     *
     * @return mixed
     */
    public function highest(): mixed
    {
        return $this->data->sortByDesc('value')->first();
    }

    /**
     * Get location with lowest value
     *
     * @return mixed
     */
    public function lowest(): mixed
    {
        return $this->data->sortBy('value')->first();
    }

    /**
     * Group by continent or region (if available)
     *
     * @param string $groupField
     * @return Collection
     */
    public function groupBy(string $groupField): Collection
    {
        return $this->data->groupBy($groupField);
    }

    /**
     * Check if location exists
     *
     * @param string $location
     * @return bool
     */
    public function hasLocation(string $location): bool
    {
        return $this->data->contains('location', $location);
    }
}
