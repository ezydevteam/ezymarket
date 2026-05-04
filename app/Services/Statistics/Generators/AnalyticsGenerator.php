<?php

namespace App\Services\Statistics\Generators;

use App\Services\Statistics\Builders\QueryBuilder;
use App\Services\Statistics\Contracts\GeneratorInterface;
use App\Traits\HandlesValidation;
use Carbon\Carbon;

/**
 * AnalyticsGenerator
 *
 * Generates monthly analytics data for time-based comparisons
 * Supports single year analytics and multi-year comparisons
 */
class AnalyticsGenerator implements GeneratorInterface
{
    use HandlesValidation;

    protected QueryBuilder $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Generate monthly analytics for a single year
     *
     * @param array $config Configuration options:
     *   - year: Year to analyze (default: current year)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function monthlyAnalytics(array $config = []): array
    {
        // Validate config
        $this->validateAnalyticsConfig($config);

        $year = $config['year'] ?? date('Y');
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        $months = [];
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = date('M', mktime(0, 0, 0, $month, 1));

            $startDate = Carbon::parse("$year-$month-01")->startOfMonth();
            $endDate = Carbon::parse("$year-$month-01")->endOfMonth();

            $query = $this->queryBuilder->clone()
                ->dateRange($startDate, $endDate, $dateField)
                ->getQuery();

            $value = match ($type) {
                'sum' => $query->sum($field),
                'avg' => $query->avg($field),
                'count' => $query->count(),
                'raw' => $query->selectRaw("{$field} as calculated_value")->value('calculated_value') ?? 0,
                default => 0,
            };

            $data[] = (float) $value;
        }

        // Get current month index (0-based) if analyzing current year
        $currentMonth = $year == date('Y') ? (int)date('n') - 1 : -1;

        return [
            'success' => true,
            'labels' => $months,
            'data' => $data,
            'currentIndex' => $currentMonth,
            'currentMonth' => $currentMonth,
            'year' => (int) $year,
            'period' => (string) $year,
            'type' => $type,
            'maxOffset' => 4, // Can go back 5 years (0-4 offset)
        ];
    }

    /**
     * Generate weekly analytics for a single week
     *
     * @param array $config Configuration options:
     *   - offset: Week offset from current (0 = current week, 1 = last week, etc.)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function weeklyAnalytics(array $config = []): array
    {
        $offset = $config['offset'] ?? 0;
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        // Calculate the start and end of the target week
        $startDate = Carbon::now()->subWeeks($offset)->startOfWeek();
        $endDate = Carbon::now()->subWeeks($offset)->endOfWeek();

        $labels = [];
        $data = [];

        // Generate data for each day of the week
        for ($day = 0; $day < 7; $day++) {
            $date = $startDate->copy()->addDays($day);
            $labels[] = $date->format('D'); // Mon, Tue, Wed, etc.

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $query = $this->queryBuilder->clone()
                ->dateRange($dayStart, $dayEnd, $dateField)
                ->getQuery();

            $value = match ($type) {
                'sum' => $query->sum($field),
                'avg' => $query->avg($field),
                'count' => $query->count(),
                default => 0,
            };

            $data[] = (float) $value;
        }

        // Get current day index (0-based) if analyzing current week
        $currentDay = ($offset === 0 && $startDate->weekOfYear == Carbon::now()->weekOfYear && $startDate->year == Carbon::now()->year)
            ? (int)Carbon::now()->dayOfWeek
            : -1;

        // Adjust for Monday start (Carbon uses 0=Sunday, we want 0=Monday)
        if ($currentDay >= 0) {
            $currentDay = ($currentDay === 0) ? 6 : $currentDay - 1;
        }

        // Calculate max offset - weeks within current month
        $monthStart = Carbon::now()->startOfMonth()->startOfWeek();
        $now = Carbon::now()->startOfWeek();
        $maxOffset = (int) $now->diffInWeeks($monthStart);

        return [
            'labels' => $labels,
            'data' => $data,
            'currentIndex' => $currentDay,
            'period' => $offset === 0 ? 'This Week' : ($offset === 1 ? 'Last Week' : $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')),
            'type' => $type,
            'maxOffset' => $maxOffset,
        ];
    }

    /**
     * Generate daily analytics for a single month
     *
     * @param array $config Configuration options:
     *   - offset: Month offset from current (0 = current month, 1 = last month, etc.)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function monthlyDailyAnalytics(array $config = []): array
    {
        $offset = $config['offset'] ?? 0;
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        // Calculate the start and end of the target month
        $startDate = Carbon::now()->subMonths($offset)->startOfMonth();
        $endDate = Carbon::now()->subMonths($offset)->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $labels = [];
        $data = [];

        // Generate data for each day of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startDate->copy()->addDays($day - 1);
            $labels[] = $date->format('d'); // 01, 02, 03, etc.

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $query = $this->queryBuilder->clone()
                ->dateRange($dayStart, $dayEnd, $dateField)
                ->getQuery();

            $value = match ($type) {
                'sum' => $query->sum($field),
                'avg' => $query->avg($field),
                'count' => $query->count(),
                default => 0,
            };

            $data[] = (float) $value;
        }

        // Get current day index (0-based) if analyzing current month
        $currentDay = ($offset === 0 && $startDate->month == Carbon::now()->month && $startDate->year == Carbon::now()->year)
            ? (int)date('j') - 1
            : -1;

        // Calculate max offset - months within current year
        $maxOffset = (int)date('n') - 1; // Current month minus 1 (e.g., December = 11 months back to January)

        return [
            'labels' => $labels,
            'data' => $data,
            'currentIndex' => $currentDay,
            'period' => $startDate->format('F Y'),
            'type' => $type,
            'maxOffset' => $maxOffset,
        ];
    }

    /**
     * Generate comparison analytics for multiple years
     *
     * @param array $config Configuration options:
     *   - years: Number of years to compare (default: 5)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function yearlyComparison(array $config = []): array
    {
        // Validate config
        $this->validateAnalyticsConfig($config);

        $yearsCount = $config['years'] ?? 5;
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        $currentYear = date('Y');
        $years = [];
        $datasets = [];

        // Get data for each year
        for ($i = $yearsCount - 1; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $years[] = (string) $year;

            $yearData = [];
            for ($month = 1; $month <= 12; $month++) {
                $startDate = Carbon::parse("$year-$month-01")->startOfMonth();
                $endDate = Carbon::parse("$year-$month-01")->endOfMonth();

                $query = $this->queryBuilder->clone()
                    ->dateRange($startDate, $endDate, $dateField)
                    ->getQuery();

                $value = match ($type) {
                    'sum' => $query->sum($field),
                    'avg' => $query->avg($field),
                    'count' => $query->count(),
                    'raw' => $query->selectRaw("{$field} as calculated_value")->value('calculated_value') ?? 0,
                    default => 0,
                };

                $yearData[] = (float) $value;
            }

            $datasets[] = [
                'label' => $year,
                'data' => $yearData,
            ];
        }

        // Generate month labels
        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $months[] = date('M', mktime(0, 0, 0, $month, 1));
        }

        return [
            'success' => true,
            'labels' => $months,
            'datasets' => $datasets,
            'years' => $years,
            'type' => $type,
        ];
    }

    /**
     * Generate weekly comparison analytics for multiple weeks
     *
     * @param array $config Configuration options:
     *   - periods: Number of weeks to compare (default: 5)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function weeklyComparison(array $config = []): array
    {
        $periodsCount = $config['periods'] ?? 5;
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $datasets = [];

        // Get data for each week (most recent first)
        for ($i = 0; $i < $periodsCount; $i++) {
            $startDate = Carbon::now()->subWeeks($i)->startOfWeek();
            $endDate = Carbon::now()->subWeeks($i)->endOfWeek();

            $weekLabel = $i === 0 ? 'This Week' : ($i === 1 ? 'Last Week' : $startDate->format('M d'));
            $weekData = [];

            // Get data for each day of the week
            for ($day = 0; $day < 7; $day++) {
                $date = $startDate->copy()->addDays($day);
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                $query = $this->queryBuilder->clone()
                    ->dateRange($dayStart, $dayEnd, $dateField)
                    ->getQuery();

                $value = match ($type) {
                    'sum' => $query->sum($field),
                    'avg' => $query->avg($field),
                    'count' => $query->count(),
                    default => 0,
                };

                $weekData[] = (float) $value;
            }

            // Insert at beginning to show most recent first
            array_unshift($datasets, [
                'label' => $weekLabel,
                'data' => $weekData,
            ]);
        }

        return [
            'labels' => $labels,
            'datasets' => array_reverse($datasets), // Reverse to show oldest to newest
            'type' => $type,
        ];
    }

    /**
     * Generate monthly comparison analytics for multiple months
     *
     * @param array $config Configuration options:
     *   - periods: Number of months to compare (default: 5)
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function monthlyComparison(array $config = []): array
    {
        $periodsCount = $config['periods'] ?? 5;
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        $datasets = [];
        $maxDays = 31; // Use maximum possible days for consistent labels
        $labels = [];

        // Generate labels for 31 days
        for ($day = 1; $day <= $maxDays; $day++) {
            $labels[] = (string) $day;
        }

        // Get data for each month (most recent first)
        for ($i = 0; $i < $periodsCount; $i++) {
            $startDate = Carbon::now()->subMonths($i)->startOfMonth();
            $endDate = Carbon::now()->subMonths($i)->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;

            $monthLabel = $startDate->format('F Y');
            $monthData = [];

            // Get data for each day of the month
            for ($day = 1; $day <= $maxDays; $day++) {
                if ($day <= $daysInMonth) {
                    $date = $startDate->copy()->addDays($day - 1);
                    $dayStart = $date->copy()->startOfDay();
                    $dayEnd = $date->copy()->endOfDay();

                    $query = $this->queryBuilder->clone()
                        ->dateRange($dayStart, $dayEnd, $dateField)
                        ->getQuery();

                    $value = match ($type) {
                        'sum' => $query->sum($field),
                        'avg' => $query->avg($field),
                        'count' => $query->count(),
                        default => 0,
                    };

                    $monthData[] = (float) $value;
                } else {
                    // Pad with null for months with fewer days
                    $monthData[] = null;
                }
            }

            $datasets[] = [
                'label' => $monthLabel,
                'data' => $monthData,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => array_reverse($datasets), // Reverse to show oldest to newest
            'type' => $type,
        ];
    }

    /**
     * Generate custom period analytics
     *
     * @param array $config Configuration options:
     *   - periods: Array of period definitions
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     * @return array
     */
    public function periodComparison(array $config = []): array
    {
        $periods = $config['periods'] ?? [];
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';

        // Validate periods
        $this->validatePeriods($periods);

        $labels = [];
        $data = [];

        foreach ($periods as $period) {
            $labels[] = $period['label'];

            $query = $this->queryBuilder->clone()
                ->dateRange($period['start'], $period['end'], $dateField)
                ->getQuery();

            $value = match ($type) {
                'sum' => $query->sum($field),
                'avg' => $query->avg($field),
                'count' => $query->count(),
                default => 0,
            };

            $data[] = (float) $value;
        }

        return [
            'success' => true,
            'labels' => $labels,
            'data' => $data,
            'type' => $type,
        ];
    }

    /**
     * Generate growth metrics comparing current vs previous period
     *
     * @param array $config Configuration options:
     *   - type: Type of aggregation (count, sum, avg)
     *   - field: Field to aggregate (required for sum/avg)
     *   - dateField: Date field to use (default: created_at)
     *   - period: Period type (day, week, month, year)
     * @return array
     */
    public function growthMetrics(array $config = []): array
    {
        // Validate config
        $this->validateGrowthConfig($config);

        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $dateField = $config['dateField'] ?? 'created_at';
        $period = $config['period'] ?? 'month';

        $now = now();

        // Define periods
        $periods = match ($period) {
            'day' => [
                'current' => ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()],
                'previous' => ['start' => $now->copy()->subDay()->startOfDay(), 'end' => $now->copy()->subDay()->endOfDay()],
            ],
            'week' => [
                'current' => ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()],
                'previous' => ['start' => $now->copy()->subWeek()->startOfWeek(), 'end' => $now->copy()->subWeek()->endOfWeek()],
            ],
            'month' => [
                'current' => ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()],
                'previous' => ['start' => $now->copy()->subMonth()->startOfMonth(), 'end' => $now->copy()->subMonth()->endOfMonth()],
            ],
            'year' => [
                'current' => ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()],
                'previous' => ['start' => $now->copy()->subYear()->startOfYear(), 'end' => $now->copy()->subYear()->endOfYear()],
            ],
            default => throw new \InvalidArgumentException("Invalid period type: {$period}"),
        };

        // Get current period value
        $currentQuery = $this->queryBuilder->clone()
            ->dateRange($periods['current']['start'], $periods['current']['end'], $dateField)
            ->getQuery();

        $currentValue = match ($type) {
            'sum' => $currentQuery->sum($field),
            'avg' => $currentQuery->avg($field),
            'count' => $currentQuery->count(),
            default => 0,
        };

        // Get previous period value
        $previousQuery = $this->queryBuilder->clone()
            ->dateRange($periods['previous']['start'], $periods['previous']['end'], $dateField)
            ->getQuery();

        $previousValue = match ($type) {
            'sum' => $previousQuery->sum($field),
            'avg' => $previousQuery->avg($field),
            'count' => $previousQuery->count(),
            default => 0,
        };

        // Calculate growth
        $growth = $previousValue > 0
            ? (($currentValue - $previousValue) / $previousValue) * 100
            : 0;

        return [
            'success' => true,
            'current' => (float) $currentValue,
            'previous' => (float) $previousValue,
            'growth' => round($growth, 2),
            'growthPercentage' => round($growth, 2) . '%',
            'period' => $period,
            'type' => $type,
        ];
    }

    /**
     * Validate analytics configuration
     *
     * @param array $config
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateAnalyticsConfig(array $config): void
    {
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';

        // Validate type
        if (!in_array($type, ['count', 'sum', 'avg', 'raw'])) {
            throw new \InvalidArgumentException("Invalid aggregation type: {$type}. Allowed: count, sum, avg, raw");
        }

        // Validate field is provided for sum/avg/raw
        if (in_array($type, ['sum', 'avg', 'raw']) && $field === '*') {
            throw new \InvalidArgumentException("Field is required for {$type} aggregation");
        }

        // Validate year if provided
        if (isset($config['year'])) {
            $year = $config['year'];
            if (!is_numeric($year) || $year < 1900 || $year > 2100) {
                throw new \InvalidArgumentException("Invalid year: {$year}");
            }
        }

        // Validate years count if provided
        if (isset($config['years'])) {
            $years = $config['years'];
            if (!is_numeric($years) || $years < 1 || $years > 20) {
                throw new \InvalidArgumentException("Invalid years count: {$years}. Must be between 1 and 20");
            }
        }
    }

    /**
     * Validate periods configuration
     *
     * @param array $periods
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validatePeriods(array $periods): void
    {
        if (empty($periods)) {
            throw new \InvalidArgumentException('Periods are required for period comparison');
        }

        foreach ($periods as $index => $period) {
            if (!isset($period['label'])) {
                throw new \InvalidArgumentException("Period at index {$index} is missing 'label'");
            }
            if (!isset($period['start'])) {
                throw new \InvalidArgumentException("Period at index {$index} is missing 'start' date");
            }
            if (!isset($period['end'])) {
                throw new \InvalidArgumentException("Period at index {$index} is missing 'end' date");
            }
        }
    }

    /**
     * Validate growth metrics configuration
     *
     * @param array $config
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateGrowthConfig(array $config): void
    {
        $type = $config['type'] ?? 'count';
        $field = $config['field'] ?? '*';
        $period = $config['period'] ?? 'month';

        // Validate type
        if (!in_array($type, ['count', 'sum', 'avg'])) {
            throw new \InvalidArgumentException("Invalid aggregation type: {$type}. Allowed: count, sum, avg");
        }

        // Validate field is provided for sum/avg
        if (in_array($type, ['sum', 'avg']) && $field === '*') {
            throw new \InvalidArgumentException("Field is required for {$type} aggregation");
        }

        // Validate period
        if (!in_array($period, ['day', 'week', 'month', 'year'])) {
            throw new \InvalidArgumentException("Invalid period type: {$period}. Allowed: day, week, month, year");
        }
    }
}
