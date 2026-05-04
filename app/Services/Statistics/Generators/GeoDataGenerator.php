<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Contracts\GeneratorInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * GeoDataGenerator
 *
 * Generates geographical statistics data grouped by countries,
 * regions, cities, or other location fields.
 */
class GeoDataGenerator implements GeneratorInterface
{
    protected QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get data grouped by country
     *
     * @param array $config
     * @return Collection
     *
     * Config options:
     * - aggregation: count, sum, avg, etc.
     * - field: Field to aggregate
     * - limit: Limit results
     * - orderBy: Order by field (total_sales, total_spend, etc.)
     */
    public function byCountry(array $config = []): Collection
    {
        $aggregation = $config['aggregation'] ?? 'count';
        $field = $config['field'] ?? '*';
        $limit = $config['limit'] ?? null;
        $orderBy = $config['orderBy'] ?? 'value';

        $query = $this->queryBuilder->clone()->getQuery();
        $query->whereNotNull('country');

        $aggregateSQL = $this->buildAggregation($aggregation, $field);
        $aliasName = $this->getAggregationAlias($aggregation, $field);

        $results = $query->selectRaw("country, {$aggregateSQL} as {$aliasName}")
            ->groupBy('country')
            ->orderByDesc($aliasName);

        if ($limit) {
            $results->limit($limit);
        }

        return $results->get();
    }

    /**
     * Get top locations by specified metric
     *
     * @param string $locationField
     * @param array $config
     * @return Collection
     */
    public function topLocations(string $locationField, array $config = []): Collection
    {
        $aggregation = $config['aggregation'] ?? 'count';
        $field = $config['field'] ?? '*';
        $limit = $config['limit'] ?? 10;

        $query = $this->queryBuilder->clone()->getQuery();
        $query->whereNotNull($locationField);

        $aggregateSQL = $this->buildAggregation($aggregation, $field);
        $aliasName = $this->getAggregationAlias($aggregation, $field);

        return $query->selectRaw("{$locationField} as location, {$aggregateSQL} as {$aliasName}")
            ->groupBy('location')
            ->orderByDesc($aliasName)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all countries with sales count
     *
     * @return Collection
     */
    public function getAllCountries(): Collection
    {
        $query = $this->queryBuilder->clone()->getQuery();

        return $query->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as total_sales')
            ->groupBy('country')
            ->orderByDesc('total_sales')
            ->get();
    }

    /**
     * Get distribution map data
     *
     * @param string $locationField
     * @param array $config
     * @return array
     */
    public function distributionMap(string $locationField, array $config = []): array
    {
        $data = $this->topLocations($locationField, $config);

        $total = $data->sum(function ($item) use ($config) {
            $aliasName = $this->getAggregationAlias(
                $config['aggregation'] ?? 'count',
                $config['field'] ?? '*'
            );
            return $item->{$aliasName};
        });

        return [
            'locations' => $data->map(function ($item) use ($config, $total) {
                $aliasName = $this->getAggregationAlias(
                    $config['aggregation'] ?? 'count',
                    $config['field'] ?? '*'
                );
                $value = $item->{$aliasName};

                return [
                    'location' => $item->location,
                    'value' => $value,
                    'percentage' => $total > 0 ? round(($value / $total) * 100, 2) : 0,
                ];
            })->toArray(),
            'total' => $total,
        ];
    }

    /**
     * Build aggregation SQL
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

    /**
     * Get the alias name for aggregation
     *
     * @param string $aggregation
     * @param string $field
     * @return string
     */
    protected function getAggregationAlias(string $aggregation, string|Expression $field): string
    {
        if ($field instanceof Expression) {
            return 'value';
        }

        if ($aggregation === 'count') {
            return 'total_count';
        }

        return match ($aggregation) {
            'sum' => "total_{$field}",
            'avg', 'average' => "avg_{$field}",
            'min', 'minimum' => "min_{$field}",
            'max', 'maximum' => "max_{$field}",
            default => 'value',
        };
    }
}
