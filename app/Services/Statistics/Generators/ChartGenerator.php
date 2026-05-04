<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Contracts\GeneratorInterface;
use App\Services\Statistics\DTOs\ChartConfig;
use App\Services\Statistics\DTOs\ChartData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Expression;
use Jenssegers\Date\Date;

/**
 * ChartGenerator
 *
 * Generates chart data for various visualization types
 * (time series, bar charts, pie charts, etc.)
 */
class ChartGenerator implements GeneratorInterface
{
    protected QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Generate a time series chart
     *
     * @param ChartConfig|array $config
     * @return ChartData|array
     *
     * Config options (if array):
     * - title: Chart title
     * - dateField: Field to group by date (default: created_at)
     * - aggregation: count, sum, avg, etc.
     * - aggregateField: Field to aggregate (if not count)
     * - dateFormat: Format for labels (default: 'd M')
     */
    public function timeSeries(ChartConfig|array $config = []): ChartData|array
    {
        // Convert array to DTO if needed
        if (is_array($config)) {
            $config = new ChartConfig(array_merge(['type' => 'timeSeries'], $config));
        }

        $config->validate();

        $title = $config->title ?? 'Time Series';
        $dateField = $config->dateField ?? $this->queryBuilder->getDateField();
        $aggregation = $config->aggregation ?? 'count';
        $aggregateField = $config->aggregateField ?? '*';
        $dateFormat = $config->dateFormat ?? 'd M';

        $startDate = $this->queryBuilder->getStartDate();
        $endDate = $this->queryBuilder->getEndDate();

        if (!$startDate || !$endDate) {
            throw new \InvalidArgumentException('Date range is required for time series charts');
        }

        // Generate date range
        $dates = chartDates($startDate, $endDate);

        // Build aggregation query
        $query = $this->queryBuilder->clone()->getQuery();

        $aggregateSQL = $this->buildAggregation($aggregation, $aggregateField);

        $data = $query->selectRaw("DATE({$dateField}) as date, {$aggregateSQL} as value")
            ->groupBy('date')
            ->pluck('value', 'date');

        // Merge with date range to fill gaps
        $chartData = $dates->merge($data);

        // Format for chart output
        $chart = [
            'title' => translate($title),
            'labels' => [],
            'data' => [],
        ];

        foreach ($chartData as $date => $value) {
            $label = Date::parse($date)->format($dateFormat);
            $chart['labels'][] = $label;
            $chart['data'][] = $value;
        }

        // Calculate max value for chart scaling
        $maxValue = !empty($chart['data']) ? max($chart['data']) : 0;
        $chart['max'] = $maxValue > 9 ? $maxValue + 2 : 10;

        // Return as DTO if config was DTO
        return $config instanceof ChartConfig ? ChartData::fromArray($chart) : $chart;
    }

    /**
     * Generate a bar chart
     *
     * @param ChartConfig|array $config
     * @return ChartData|array
     */
    public function barChart(ChartConfig|array $config = []): ChartData|array
    {
        // Convert array to DTO if needed
        if (is_array($config)) {
            $config = new ChartConfig(array_merge(['type' => 'bar'], $config));
        }

        $config->validate();

        $title = $config->title ?? 'Bar Chart';
        $groupBy = $config->groupBy ?? 'id';
        $aggregation = $config->aggregation ?? 'count';
        $aggregateField = $config->aggregateField ?? '*';
        $limit = $config->limit ?? null;
        $orderBy = $config->orderBy ?? 'value';
        $orderDirection = $config->orderDirection ?? 'desc';

        $query = $this->queryBuilder->clone()->getQuery();

        $aggregateSQL = $this->buildAggregation($aggregation, $aggregateField);

        $data = $query->selectRaw("{$groupBy} as label, {$aggregateSQL} as value")
            ->groupBy('label')
            ->orderBy('value', $orderDirection);

        if ($limit) {
            $data->limit($limit);
        }

        $results = $data->get();

        $chart = [
            'title' => translate($title),
            'labels' => $results->pluck('label')->toArray(),
            'data' => $results->pluck('value')->toArray(),
        ];

        // Return as DTO if config was DTO
        return $config instanceof ChartConfig ? ChartData::fromArray($chart) : $chart;
    }

    /**
     * Generate a pie chart
     *
     * @param ChartConfig|array $config
     * @return ChartData|array
     */
    public function pieChart(ChartConfig|array $config = []): ChartData|array
    {
        // Convert array to DTO if needed
        if (is_array($config)) {
            $config = new ChartConfig(array_merge(['type' => 'pie'], $config));
        }

        $barData = $this->barChart($config);

        // If barData is a DTO, convert to array for processing
        $dataArray = $barData instanceof ChartData ? $barData->toArray() : $barData;

        $total = array_sum($dataArray['data']);
        $percentages = [];

        foreach ($dataArray['data'] as $value) {
            $percentages[] = $total > 0 ? round(($value / $total) * 100, 1) : 0;
        }

        $chart = [
            'title' => $dataArray['title'],
            'labels' => $dataArray['labels'],
            'data' => $dataArray['data'],
            'percentages' => $percentages,
        ];

        // Return as DTO if config was DTO
        return $config instanceof ChartConfig ? ChartData::fromArray($chart) : $chart;
    }

    /**
     * Build aggregation SQL based on type
     *
     * @param string $aggregation
     * @param string $field
     * @return string
     */
    protected function buildAggregation(string $aggregation, string|Expression $field): string
    {
        if ($field instanceof Expression) {
            $field = $field->getValue(DB::connection()->getQueryGrammar());
        }

        return match ($aggregation) {
            'count' => "COUNT({$field})",
            'sum' => "SUM({$field})",
            'avg', 'average' => "AVG({$field})",
            'min', 'minimum' => "MIN({$field})",
            'max', 'maximum' => "MAX({$field})",
            default => "COUNT(*)",
        };
    }
}
