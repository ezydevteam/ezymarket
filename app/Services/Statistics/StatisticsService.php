<?php

namespace App\Services\Statistics;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Generators\{
    CounterGenerator,
    ChartGenerator,
    GeoDataGenerator,
    ReferralGenerator,
    AnalyticsGenerator,
    ExcelExportGenerator,
    PdfExportGenerator
};
use App\Services\Statistics\DTOs\{ExportConfig, ExportData};
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Expression;
use Carbon\Carbon;

/**
 * StatisticsService
 *
 * Main service class providing a fluent interface for generating
 * various types of statistics, charts, and analytics data.
 *
 * Usage:
 * $stats = app(StatisticsService::class)
 *     ->forModel(Sale::class)
 *     ->where('product_id', $productId)
 *     ->dateRange($startDate, $endDate);
 *
 * $counters = $stats->counters(['total_sales' => ['count', '*']]);
 * $chart = $stats->chart('timeSeries', ['title' => 'Sales']);
 */
class StatisticsService
{
    protected QueryBuilder $queryBuilder;
    protected ?CounterGenerator $counterGenerator = null;
    protected ?ChartGenerator $chartGenerator = null;
    protected ?GeoDataGenerator $geoDataGenerator = null;
    protected ?ReferralGenerator $referralGenerator = null;
    protected ?AnalyticsGenerator $analyticsGenerator = null;
    protected ?ExcelExportGenerator $excelExportGenerator = null;
    protected ?PdfExportGenerator $pdfExportGenerator = null;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilder();
    }

    /**
     * Set the model class for statistics
     *
     * @param string $modelClass
     * @param mixed $modelId
     * @return self
     */
    public function forModel(string $modelClass, $modelId = null): self
    {
        $this->queryBuilder->forModel($modelClass, $modelId);
        $this->resetGenerators();
        return $this;
    }

    /**
     * Add a where condition
     *
     * @param string $field
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function where(string $field, $operator, $value = null): self
    {
        $this->queryBuilder->where($field, $operator, $value);
        return $this;
    }

    /**
     * Add a whereIn condition
     *
     * @param string $field
     * @param array $values
     * @return self
     */
    public function whereIn(string $field, array $values): self
    {
        $this->queryBuilder->whereIn($field, $values);
        return $this;
    }

    /**
     * Add a whereNotNull condition
     *
     * @param string $field
     * @return self
     */
    public function whereNotNull(string $field): self
    {
        $this->queryBuilder->whereNotNull($field);
        return $this;
    }

    /**
     * Add a whereHas condition (for filtering by relationships)
     *
     * @param string $relation
     * @param \Closure|null $callback
     * @param string $operator
     * @param int $count
     * @return self
     */
    public function whereHas(string $relation, \Closure $callback = null, string $operator = '>=', int $count = 1): self
    {
        $this->queryBuilder->whereHas($relation, $callback, $operator, $count);
        return $this;
    }

    /**
     * Add a whereDoesntHave condition
     *
     * @param string $relation
     * @param \Closure|null $callback
     * @return self
     */
    public function whereDoesntHave(string $relation, \Closure $callback = null): self
    {
        $this->queryBuilder->whereDoesntHave($relation, $callback);
        return $this;
    }

    /**
     * Set the date range for statistics
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dateField
     * @return self
     */
    public function dateRange(Carbon $startDate, Carbon $endDate, string $dateField = 'created_at'): self
    {
        $this->queryBuilder->dateRange($startDate, $endDate, $dateField);
        return $this;
    }

    /**
     * Add eager loading relationships
     *
     * @param array $relations
     * @return self
     */
    public function with(array $relations): self
    {
        $this->queryBuilder->withRelations($relations);
        return $this;
    }

    /**
     * Add a scope to the query
     *
     * @param string $scope
     * @param mixed ...$parameters
     * @return self
     */
    public function scope(string $scope, ...$parameters): self
    {
        $this->queryBuilder->withScope($scope, ...$parameters);
        return $this;
    }

    /**
     * Generate counters
     *
     * @param array $counters
     * @return array
     */
    public function counters(array $counters): array
    {
        return $this->getCounterGenerator()->generate($counters);
    }

    /**
     * Generate a chart
     *
     * @param string $type
     * @param array $config
     * @return array
     */
    public function chart(string $type, array $config = []): array
    {
        $generator = $this->getChartGenerator();

        $result = match ($type) {
            'timeSeries', 'line' => $generator->timeSeries($config),
            'bar' => $generator->barChart($config),
            'pie' => $generator->pieChart($config),
            default => throw new \InvalidArgumentException("Unknown chart type: {$type}"),
        };

        // Convert DTO to array if needed
        return is_array($result) ? $result : $result->toArray();
    }

    /**
     * Generate geographical data
     *
     * @param string $type
     * @param array $config
     * @return mixed
     */
    public function geoData(string $type = 'byCountry', array $config = [])
    {
        $generator = $this->getGeoDataGenerator();

        return match ($type) {
            'byCountry' => $generator->byCountry($config),
            'topLocations' => $generator->topLocations($config['field'] ?? 'country', $config),
            'distribution' => $generator->distributionMap($config['field'] ?? 'country', $config),
            'allCountries' => $generator->getAllCountries(),
            default => $generator->byCountry($config),
        };
    }

    /**
     * Generate referral data
     *
     * @param array $config
     * @return mixed
     */
    public function referrals(array $config = [])
    {
        return $this->getReferralGenerator()->topReferrers($config);
    }

    /**
     * Get referrer distribution
     *
     * @param array $config
     * @return array
     */
    public function referrerDistribution(array $config = []): array
    {
        return $this->getReferralGenerator()->referrerDistribution($config);
    }

    /**
     * Get traffic sources
     *
     * @param string $field
     * @return array
     */
    public function trafficSources(string $field = 'referrer'): array
    {
        return $this->getReferralGenerator()->trafficSources($field);
    }

    /**
     * Get top items by a field with aggregations
     *
     * @param string $groupByField Field to group by (e.g., 'product_id', 'user_id')
     * @param array $config Configuration array
     * @return \Illuminate\Support\Collection
     *
     * Config options:
     * - aggregations: Array of aggregations ['total_sales' => ['count', '*'], 'total_earnings' => ['sum', 'price']]
     * - orderBy: Field to order by (default: first aggregation)
     * - orderDirection: 'asc' or 'desc' (default: 'desc')
     * - limit: Number of results (default: 10)
     */
    public function topItems(string $groupByField, array $config = [])
    {
        $aggregations = $config['aggregations'] ?? ['total' => ['count', '*']];
        $limit = $config['limit'] ?? 10;
        $orderBy = $config['orderBy'] ?? array_key_first($aggregations);
        $orderDirection = $config['orderDirection'] ?? 'desc';

        $query = $this->queryBuilder->clone()->getQuery();

        // Build select with group by field and aggregations
        $selectFields = [$groupByField];
        foreach ($aggregations as $alias => $agg) {
            $aggregation = $agg[0] ?? 'count';
            $field = $agg[1] ?? '*';
            $selectFields[] = DB::raw($this->buildAggregationSql($aggregation, $field) . " as {$alias}");
        }

        $query->select($selectFields)
            ->groupBy($groupByField)
            ->orderBy($orderBy, $orderDirection)
            ->limit($limit);

        return $query->get();
    }

    /**
     * Build aggregation SQL
     *
     * @param string $aggregation
     * @param string $field
     * @return string
     */
    protected function buildAggregationSql(string $aggregation, string|Expression $field): string
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

    /**
     * Export statistics data in various formats (Excel, PDF)
     *
     * @param string $type Export type: 'excel' or 'pdf'
     * @param array $config Export configuration
     * @return ExportData
     *
     * Example:
     * $export = $stats->forModel(Sale::class)
     *     ->dateRange($startDate, $endDate)
     *     ->export('excel', [
     *         'filename' => 'sales-report',
     *         'format' => 'download', // or 'store', 'stream'
     *         'columns' => ['date', 'product', 'amount'],
     *         'headers' => ['Date', 'Product', 'Amount']
     *     ]);
     */
    public function export(string $type, array $config = []): ExportData
    {
        $exportConfig = ExportConfig::fromArray($config);

        return match (strtolower($type)) {
            'excel', 'xlsx' => $this->getExcelExportGenerator()->generate($exportConfig),
            'pdf' => $this->getPdfExportGenerator()->generate($exportConfig),
            default => throw new \InvalidArgumentException("Invalid export type: {$type}. Supported: excel, pdf"),
        };
    }

    /**
     * Generate weekly analytics for a single week
     *
     * @param array $config Configuration options
     * @return array
     */
    public function weeklyAnalytics(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->weeklyAnalytics($config);
    }

    /**
     * Generate monthly analytics (12 months for a year)
     *
     * @param array $config Configuration options
     * @return array
     */
    public function monthlyAnalytics(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->monthlyAnalytics($config);
    }

    /**
     * Generate daily analytics for a single month
     *
     * @param array $config Configuration options
     * @return array
     */
    public function monthlyDailyAnalytics(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->monthlyDailyAnalytics($config);
    }

    /**
     * Generate comparison analytics for multiple years
     *
     * @param array $config Configuration options
     * @return array
     */
    public function yearlyComparison(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->yearlyComparison($config);
    }

    /**
     * Generate weekly comparison analytics for multiple weeks
     *
     * @param array $config Configuration options
     * @return array
     */
    public function weeklyComparison(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->weeklyComparison($config);
    }

    /**
     * Generate monthly comparison analytics for multiple months
     *
     * @param array $config Configuration options
     * @return array
     */
    public function monthlyComparison(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->monthlyComparison($config);
    }

    /**
     * Generate custom period analytics
     *
     * @param array $config Configuration options
     * @return array
     */
    public function periodComparison(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->periodComparison($config);
    }

    /**
     * Generate growth metrics comparing current vs previous period
     *
     * @param array $config Configuration options
     * @return array
     */
    public function growthMetrics(array $config = []): array
    {
        return $this->getAnalyticsGenerator()->growthMetrics($config);
    }

    /**
     * Get the underlying query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * Get or create counter generator
     *
     * @return CounterGenerator
     */
    protected function getCounterGenerator(): CounterGenerator
    {
        if (!$this->counterGenerator) {
            $this->counterGenerator = new CounterGenerator($this->queryBuilder);
        }
        return $this->counterGenerator;
    }

    /**
     * Get or create chart generator
     *
     * @return ChartGenerator
     */
    protected function getChartGenerator(): ChartGenerator
    {
        if (!$this->chartGenerator) {
            $this->chartGenerator = new ChartGenerator($this->queryBuilder);
        }
        return $this->chartGenerator;
    }

    /**
     * Get or create geo data generator
     *
     * @return GeoDataGenerator
     */
    protected function getGeoDataGenerator(): GeoDataGenerator
    {
        if (!$this->geoDataGenerator) {
            $this->geoDataGenerator = new GeoDataGenerator($this->queryBuilder);
        }
        return $this->geoDataGenerator;
    }

    /**
     * Get or create referral generator
     *
     * @return ReferralGenerator
     */
    protected function getReferralGenerator(): ReferralGenerator
    {
        if (!$this->referralGenerator) {
            $this->referralGenerator = new ReferralGenerator($this->queryBuilder);
        }
        return $this->referralGenerator;
    }

    /**
     * Get or create analytics generator
     *
     * @return AnalyticsGenerator
     */
    protected function getAnalyticsGenerator(): AnalyticsGenerator
    {
        if (!$this->analyticsGenerator) {
            $this->analyticsGenerator = new AnalyticsGenerator($this->queryBuilder);
        }
        return $this->analyticsGenerator;
    }

    /**
     * Get or create Excel export generator
     *
     * @return ExcelExportGenerator
     */
    protected function getExcelExportGenerator(): ExcelExportGenerator
    {
        if (!$this->excelExportGenerator) {
            $this->excelExportGenerator = new ExcelExportGenerator($this->queryBuilder);
        }
        return $this->excelExportGenerator;
    }

    /**
     * Get or create PDF export generator
     *
     * @return PdfExportGenerator
     */
    protected function getPdfExportGenerator(): PdfExportGenerator
    {
        if (!$this->pdfExportGenerator) {
            $this->pdfExportGenerator = new PdfExportGenerator($this->queryBuilder);
        }
        return $this->pdfExportGenerator;
    }

    /**
     * Reset generators when model changes
     *
     * @return void
     */
    protected function resetGenerators(): void
    {
        $this->counterGenerator = null;
        $this->chartGenerator = null;
        $this->geoDataGenerator = null;
        $this->referralGenerator = null;
        $this->analyticsGenerator = null;
        $this->excelExportGenerator = null;
        $this->pdfExportGenerator = null;
    }
}
