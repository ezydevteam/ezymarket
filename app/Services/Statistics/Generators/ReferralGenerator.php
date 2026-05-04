<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Contracts\GeneratorInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

/**
 * ReferralGenerator
 *
 * Generates referral and traffic source statistics.
 */
class ReferralGenerator implements GeneratorInterface
{
    protected QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get top referrers
     *
     * @param array $config
     * @return Collection
     *
     * Config options:
     * - field: Field containing referrer data (default: referrer)
     * - limit: Number of results (default: 10)
     * - aggregation: count, sum, etc.
     * - aggregateField: Field to aggregate
     */
    public function topReferrers(array $config = []): Collection
    {
        $field = $config['field'] ?? 'referrer';
        $limit = $config['limit'] ?? 10;
        $aggregation = $config['aggregation'] ?? 'count';
        $aggregateField = $config['aggregateField'] ?? '*';

        $query = $this->queryBuilder->clone()->getQuery();
        $query->whereNotNull($field);

        $aggregateSQL = $this->buildAggregation($aggregation, $aggregateField);

        return $query->selectRaw("{$field} as referrer, {$aggregateSQL} as total_views")
            ->groupBy('referrer')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get();
    }

    /**
     * Get referrer distribution with percentages
     *
     * @param array $config
     * @return array
     */
    public function referrerDistribution(array $config = []): array
    {
        $referrers = $this->topReferrers($config);
        $total = $referrers->sum('total_views');

        return [
            'referrers' => $referrers->map(function ($item) use ($total) {
                return [
                    'referrer' => $item->referrer,
                    'views' => $item->total_views,
                    'percentage' => $total > 0 ? round(($item->total_views / $total) * 100, 2) : 0,
                ];
            })->toArray(),
            'total' => $total,
        ];
    }

    /**
     * Get traffic sources categorized
     *
     * @param string $field
     * @return array
     */
    public function trafficSources(string $field = 'referrer'): array
    {
        $query = $this->queryBuilder->clone()->getQuery();
        $referrers = $query->whereNotNull($field)
            ->select($field)
            ->groupBy($field)
            ->selectRaw("COUNT(*) as count")
            ->get();

        $categories = [
            'social' => 0,
            'search' => 0,
            'direct' => 0,
            'email' => 0,
            'other' => 0,
        ];

        foreach ($referrers as $referrer) {
            $source = strtolower($referrer->{$field});

            if ($this->isSocialMedia($source)) {
                $categories['social'] += $referrer->count;
            } elseif ($this->isSearchEngine($source)) {
                $categories['search'] += $referrer->count;
            } elseif ($this->isEmail($source)) {
                $categories['email'] += $referrer->count;
            } elseif (empty($source) || $source === 'direct') {
                $categories['direct'] += $referrer->count;
            } else {
                $categories['other'] += $referrer->count;
            }
        }

        $total = array_sum($categories);

        return [
            'categories' => $categories,
            'total' => $total,
            'percentages' => array_map(function ($count) use ($total) {
                return $total > 0 ? round(($count / $total) * 100, 2) : 0;
            }, $categories),
        ];
    }

    /**
     * Check if referrer is social media
     *
     * @param string $referrer
     * @return bool
     */
    protected function isSocialMedia(string $referrer): bool
    {
        $socialPlatforms = ['facebook', 'twitter', 'instagram', 'linkedin', 'pinterest', 'tiktok', 'youtube', 'reddit'];

        foreach ($socialPlatforms as $platform) {
            if (str_contains($referrer, $platform)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if referrer is search engine
     *
     * @param string $referrer
     * @return bool
     */
    protected function isSearchEngine(string $referrer): bool
    {
        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];

        foreach ($searchEngines as $engine) {
            if (str_contains($referrer, $engine)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if referrer is email
     *
     * @param string $referrer
     * @return bool
     */
    protected function isEmail(string $referrer): bool
    {
        return str_contains($referrer, 'mail') || str_contains($referrer, 'email');
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
            default => "COUNT(*)",
        };
    }
}
