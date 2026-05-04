<?php

namespace App\Services\Statistics\DTOs;
use Illuminate\Contracts\Database\Query\Expression;

/**
 * ChartConfig
 *
 * Data Transfer Object for chart configuration.
 * Provides type safety and validation for chart generation.
 */
class ChartConfig
{
    public string $title;
    public string $type;
    public string $aggregation;
    public string|Expression $aggregateField;
    public ?string $dateField;
    public ?string $dateFormat;
    public ?string $groupBy;
    public ?int $limit;
    public string $orderBy;
    public string $orderDirection;

    public function __construct(array $data = [])
    {
        $this->title = $data['title'] ?? 'Chart';
        $this->type = $data['type'] ?? 'timeSeries';
        $this->aggregation = $data['aggregation'] ?? 'count';
        $this->aggregateField = $data['aggregateField'] ?? '*';
        $this->dateField = $data['dateField'] ?? null;
        $this->dateFormat = $data['dateFormat'] ?? 'd M';
        $this->groupBy = $data['groupBy'] ?? null;
        $this->limit = $data['limit'] ?? null;
        $this->orderBy = $data['orderBy'] ?? 'value';
        $this->orderDirection = $data['orderDirection'] ?? 'desc';
    }

    /**
     * Create a time series chart config
     *
     * @param string $title
     * @param string $dateField
     * @param string $aggregation
     * @param string|Expression $aggregateField
     * @return self
     */
    public static function timeSeries(
        string $title,
        string $dateField = 'created_at',
        string $aggregation = 'count',
        string|Expression $aggregateField = '*'
    ): self {
        return new self([
            'type' => 'timeSeries',
            'title' => $title,
            'dateField' => $dateField,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
        ]);
    }

    /**
     * Create a bar chart config
     *
     * @param string $title
     * @param string $groupBy
     * @param string $aggregation
     * @param string $aggregateField
     * @param int|null $limit
     * @return self
     */
    public static function bar(
        string $title,
        string $groupBy,
        string $aggregation = 'count',
        string $aggregateField = '*',
        ?int $limit = null
    ): self {
        return new self([
            'type' => 'bar',
            'title' => $title,
            'groupBy' => $groupBy,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
            'limit' => $limit,
        ]);
    }

    /**
     * Create a pie chart config
     *
     * @param string $title
     * @param string $groupBy
     * @param string $aggregation
     * @param string $aggregateField
     * @return self
     */
    public static function pie(
        string $title,
        string $groupBy,
        string $aggregation = 'count',
        string $aggregateField = '*'
    ): self {
        return new self([
            'type' => 'pie',
            'title' => $title,
            'groupBy' => $groupBy,
            'aggregation' => $aggregation,
            'aggregateField' => $aggregateField,
        ]);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'aggregation' => $this->aggregation,
            'aggregateField' => $this->aggregateField,
            'dateField' => $this->dateField,
            'dateFormat' => $this->dateFormat,
            'groupBy' => $this->groupBy,
            'limit' => $this->limit,
            'orderBy' => $this->orderBy,
            'orderDirection' => $this->orderDirection,
        ];
    }

    /**
     * Validate the configuration
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate(): bool
    {
        if ($this->type === 'timeSeries' && !$this->dateField) {
            throw new \InvalidArgumentException('Date field is required for time series charts');
        }

        if (in_array($this->type, ['bar', 'pie']) && !$this->groupBy) {
            throw new \InvalidArgumentException('Group by field is required for bar and pie charts');
        }

        $validAggregations = ['count', 'sum', 'avg', 'average', 'min', 'max', 'minimum', 'maximum'];
        if (!in_array($this->aggregation, $validAggregations)) {
            throw new \InvalidArgumentException("Invalid aggregation: {$this->aggregation}");
        }

        return true;
    }
}
