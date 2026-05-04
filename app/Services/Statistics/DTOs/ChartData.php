<?php

namespace App\Services\Statistics\DTOs;

/**
 * ChartData
 *
 * Data Transfer Object for chart data response.
 * Standardizes chart output format.
 */
class ChartData
{
    public string $title;
    public array $labels;
    public array $data;
    public ?int $max;
    public ?array $percentages;
    public ?string $type;

    public function __construct(
        string $title,
        array $labels,
        array $data,
        ?int $max = null,
        ?array $percentages = null,
        ?string $type = null
    ) {
        $this->title = $title;
        $this->labels = $labels;
        $this->data = $data;
        $this->max = $max;
        $this->percentages = $percentages;
        $this->type = $type;
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
            $data['title'] ?? '',
            $data['labels'] ?? [],
            $data['data'] ?? [],
            $data['max'] ?? null,
            $data['percentages'] ?? null,
            $data['type'] ?? null
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
            'title' => $this->title,
            'labels' => $this->labels,
            'data' => $this->data,
        ];

        if ($this->max !== null) {
            $array['max'] = $this->max;
        }

        if ($this->percentages !== null) {
            $array['percentages'] = $this->percentages;
        }

        if ($this->type !== null) {
            $array['type'] = $this->type;
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
     * Get total data points
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get sum of all data values
     *
     * @return float
     */
    public function sum(): float
    {
        return array_sum($this->data);
    }

    /**
     * Get average of data values
     *
     * @return float
     */
    public function average(): float
    {
        $count = $this->count();
        return $count > 0 ? $this->sum() / $count : 0;
    }
}
