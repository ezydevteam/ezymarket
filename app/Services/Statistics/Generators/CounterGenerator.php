<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Contracts\GeneratorInterface;
use Illuminate\Support\Facades\DB;

/**
 * CounterGenerator
 *
 * Generates statistical counters (count, sum, avg, min, max)
 * from query builder results.
 */
class CounterGenerator implements GeneratorInterface
{
    protected QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Generate multiple counters at once
     *
     * @param array $counters
     * @return array
     *
     * Example:
     * [
     *     'total_sales' => ['count', '*'],
     *     'total_revenue' => ['sum', 'price'],
     *     'avg_rating' => ['avg', 'rating'],
     * ]
     */
    public function generate(array $counters): array
    {
        $results = [];

        foreach ($counters as $key => $config) {
            $aggregation = $config[0] ?? 'count';
            $field = $config[1] ?? '*';

            $results[$key] = $this->calculate($aggregation, $field);
        }

        return $results;
    }

    /**
     * Calculate a single counter
     *
     * @param string $aggregation
     * @param string $field
     * @return mixed
     */
    public function calculate(string $aggregation, $field = '*')
    {
        $query = $this->queryBuilder->clone()->getQuery();

        return match ($aggregation) {
            'count' => $query->count($field),
            'sum' => $query->sum($field) ?? 0,
            'avg', 'average' => round($query->avg($field) ?? 0, 2),
            'min', 'minimum' => $query->min($field) ?? 0,
            'max', 'maximum' => $query->max($field) ?? 0,
            default => $query->count(),
        };
    }

    /**
     * Execute a custom SQL aggregation
     *
     * @param string $sql
     * @param array $bindings
     * @return mixed
     */
    public function custom(string $sql, array $bindings = [])
    {
        return DB::selectOne($sql, $bindings);
    }

    /**
     * Get percentage calculation between two values
     *
     * @param float $value
     * @param float $total
     * @param int $decimals
     * @return float
     */
    public function percentage(float $value, float $total, int $decimals = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($value / $total) * 100, $decimals);
    }

    /**
     * Calculate growth rate between two periods
     *
     * @param float $current
     * @param float $previous
     * @return float
     */
    public function growthRate(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
