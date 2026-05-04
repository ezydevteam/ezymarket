<?php

namespace App\Services\Statistics\DTOs;

/**
 * CounterData
 *
 * Data Transfer Object for counter/statistic data response.
 * Standardizes counter output format.
 */
class CounterData
{
    public string $name;
    public float|int $value;
    public ?string $label;
    public ?string $icon;
    public ?string $color;
    public ?float $percentage;
    public ?float $growth;

    public function __construct(
        string $name,
        float|int $value,
        ?string $label = null,
        ?string $icon = null,
        ?string $color = null,
        ?float $percentage = null,
        ?float $growth = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->label = $label ?? $name;
        $this->icon = $icon;
        $this->color = $color;
        $this->percentage = $percentage;
        $this->growth = $growth;
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
            $data['name'] ?? '',
            $data['value'] ?? 0,
            $data['label'] ?? null,
            $data['icon'] ?? null,
            $data['color'] ?? null,
            $data['percentage'] ?? null,
            $data['growth'] ?? null
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
            'name' => $this->name,
            'value' => $this->value,
            'label' => $this->label,
        ];

        if ($this->icon !== null) {
            $array['icon'] = $this->icon;
        }

        if ($this->color !== null) {
            $array['color'] = $this->color;
        }

        if ($this->percentage !== null) {
            $array['percentage'] = $this->percentage;
        }

        if ($this->growth !== null) {
            $array['growth'] = $this->growth;
        }

        return $array;
    }

    /**
     * Format value with suffix
     *
     * @return string
     */
    public function formatted(): string
    {
        if ($this->value >= 1000000) {
            return round($this->value / 1000000, 1) . 'M';
        } elseif ($this->value >= 1000) {
            return round($this->value / 1000, 1) . 'K';
        }

        return (string) $this->value;
    }

    /**
     * Check if growth is positive
     *
     * @return bool
     */
    public function isPositiveGrowth(): bool
    {
        return $this->growth !== null && $this->growth > 0;
    }

    /**
     * Check if growth is negative
     *
     * @return bool
     */
    public function isNegativeGrowth(): bool
    {
        return $this->growth !== null && $this->growth < 0;
    }

    /**
     * Get growth trend
     *
     * @return string (up, down, stable)
     */
    public function trend(): string
    {
        if ($this->growth === null || $this->growth == 0) {
            return 'stable';
        }

        return $this->growth > 0 ? 'up' : 'down';
    }
}
