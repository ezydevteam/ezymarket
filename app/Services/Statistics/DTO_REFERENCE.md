# Complete DTOs Reference - Statistics Service

## Overview

Complete set of Data Transfer Objects (DTOs) for type-safe statistics generation and response handling.

---

## Configuration DTOs

Configuration DTOs provide type-safe input for statistics generators.

### ChartConfig

**Purpose:** Configure chart generation (time series, bar, pie charts)

**Factory Methods:**
- `ChartConfig::timeSeries($title, $dateField, $aggregation, $aggregateField)`
- `ChartConfig::bar($title, $groupBy, $aggregation, $aggregateField, $limit)`
- `ChartConfig::pie($title, $groupBy, $aggregation, $aggregateField)`

**Properties:**
- `string $title` - Chart title
- `string $type` - Chart type (timeSeries, bar, pie)
- `string $aggregation` - Aggregation type (count, sum, avg, etc.)
- `string $aggregateField` - Field to aggregate
- `?string $dateField` - Date field for time series
- `string $dateFormat` - Date label format
- `?string $groupBy` - Field to group by
- `?int $limit` - Result limit
- `string $orderBy` - Order by field
- `string $orderDirection` - asc or desc

**Example:**
```php
use App\Services\Statistics\DTOs\ChartConfig;

$config = ChartConfig::timeSeries(
    title: 'Daily Sales',
    dateField: 'created_at',
    aggregation: 'sum',
    aggregateField: 'total'
);

$config->validate(); // Throws exception if invalid
```

---

### GeoDataConfig

**Purpose:** Configure geographical statistics generation

**Factory Methods:**
- `GeoDataConfig::byCountry($locationField, $aggregation, $aggregateField, $limit)`
- `GeoDataConfig::topLocations($locationField, $limit, $aggregation, $aggregateField)`
- `GeoDataConfig::distributionMap($locationField, $aggregation, $aggregateField)`

**Properties:**
- `string $locationField` - Location field name (country, city, region)
- `string $aggregation` - Aggregation type
- `string $aggregateField` - Field to aggregate
- `?int $limit` - Result limit
- `string $orderBy` - Order by field
- `string $orderDirection` - asc or desc
- `bool $withPercentages` - Include percentage calculations
- `?string $countryCodeField` - Country code field

**Fluent Methods:**
- `withLimit(int $limit)` - Set limit
- `orderBy(string $field, string $direction)` - Set ordering
- `withPercentages(bool $enabled)` - Enable/disable percentages

**Example:**
```php
use App\Services\Statistics\DTOs\GeoDataConfig;

// Top 10 countries by sales
$config = GeoDataConfig::topLocations(
    locationField: 'country',
    limit: 10,
    aggregation: 'sum',
    aggregateField: 'total'
);

// Or with fluent interface
$config = GeoDataConfig::byCountry()
    ->withLimit(10)
    ->orderBy('value', 'desc')
    ->withPercentages(true);

$config->validate();
```

---

### ReferralConfig

**Purpose:** Configure referral/traffic source statistics

**Factory Methods:**
- `ReferralConfig::topReferrers($referrerField, $limit)`
- `ReferralConfig::distribution($referrerField, $limit)`
- `ReferralConfig::trafficSources($referrerField)`

**Properties:**
- `string $referrerField` - Referrer field name
- `?int $limit` - Result limit
- `string $orderBy` - Order by field (count, referrer)
- `string $orderDirection` - asc or desc
- `bool $withPercentages` - Include percentage calculations
- `bool $categorize` - Categorize referrers (social, search, email, etc.)
- `array $categories` - Category definitions

**Fluent Methods:**
- `withLimit(int $limit)` - Set limit
- `orderBy(string $field, string $direction)` - Set ordering
- `withPercentages(bool $enabled)` - Enable/disable percentages
- `withCategorization(bool $enabled)` - Enable/disable categorization
- `withCategories(array $categories)` - Set custom categories
- `addCategory(string $name, array $keywords)` - Add single category

**Example:**
```php
use App\Services\Statistics\DTOs\ReferralConfig;

// Top 10 referrers
$config = ReferralConfig::topReferrers('referrer', 10);

// Traffic sources with categorization
$config = ReferralConfig::trafficSources('referrer');

// Custom configuration
$config = (new ReferralConfig())
    ->withLimit(20)
    ->withCategorization(true)
    ->addCategory('marketplace', ['amazon', 'ebay', 'etsy']);

$config->validate();
```

---

## Response DTOs

Response DTOs provide type-safe structured output from statistics generators.

### ChartData

**Purpose:** Standardized chart data response

**Properties:**
- `string $title` - Chart title
- `array $labels` - Chart labels
- `array $data` - Chart data points
- `?int $max` - Maximum value for scaling
- `?array $percentages` - Percentage values (for pie charts)
- `?string $type` - Chart type

**Methods:**
- `fromArray(array $data): self` - Create from array
- `toArray(): array` - Convert to array
- `toJson(): string` - Convert to JSON
- `count(): int` - Number of data points
- `sum(): float` - Total sum of values
- `average(): float` - Average value

**Example:**
```php
use App\Services\Statistics\DTOs\ChartData;

$chartData = $stats->forModel(Order::class)
    ->chart()->timeSeries($config);

echo $chartData->title;         // "Daily Sales"
echo $chartData->count();       // 30
echo $chartData->sum();         // 15000.00
echo $chartData->average();     // 500.00

// Convert for view
return view('dashboard', [
    'chart' => $chartData->toArray()
]);
```

---

### CounterData

**Purpose:** Standardized counter/statistic response

**Properties:**
- `string $name` - Counter name
- `float|int $value` - Counter value
- `?string $label` - Display label
- `?string $icon` - Icon class
- `?string $color` - Color class
- `?float $percentage` - Percentage value
- `?float $growth` - Growth rate

**Methods:**
- `fromArray(array $data): self` - Create from array
- `toArray(): array` - Convert to array
- `formatted(): string` - Format with K/M suffix (e.g., "15K", "2.5M")
- `trend(): string` - Get trend (up, down, stable)
- `isPositiveGrowth(): bool` - Check positive growth
- `isNegativeGrowth(): bool` - Check negative growth

**Example:**
```php
use App\Services\Statistics\DTOs\CounterData;

$counter = new CounterData(
    name: 'total_sales',
    value: 15000,
    label: 'Total Sales',
    icon: 'fas fa-dollar-sign',
    color: 'success',
    growth: 12.3
);

echo $counter->formatted();         // "15K"
echo $counter->trend();             // "up"
echo $counter->isPositiveGrowth();  // true
```

---

### GeoData

**Purpose:** Standardized geographical statistics response

**Properties:**
- `Collection $data` - Collection of location data
- `?float $total` - Total value across all locations
- `?array $percentages` - Percentage distribution
- `?string $locationField` - Location field name

**Methods:**
- `fromArray(array $data): self` - Create from array
- `toArray(): array` - Convert to array
- `toJson(): string` - Convert to JSON
- `count(): int` - Number of locations
- `top(int $limit): Collection` - Get top N locations
- `above(float $threshold): Collection` - Locations above threshold
- `findByLocation(string $location): mixed` - Find specific location
- `locations(): array` - All location names
- `values(): array` - All values
- `sum(): float` - Total sum
- `average(): float` - Average value
- `highest(): mixed` - Location with highest value
- `lowest(): mixed` - Location with lowest value
- `groupBy(string $field): Collection` - Group by field
- `hasLocation(string $location): bool` - Check if location exists

**Example:**
```php
use App\Services\Statistics\DTOs\GeoData;

$geoData = $stats->forModel(Order::class)
    ->geoData()->byCountry($config);

echo $geoData->count();             // 25 countries
echo $geoData->sum();               // Total sales across all countries

$topCountries = $geoData->top(5);   // Top 5 countries
$highest = $geoData->highest();     // Country with most sales

// Find specific country
$usa = $geoData->findByLocation('United States');
echo $usa['value'];                 // Sales in USA

// Filter locations
$highValue = $geoData->above(1000); // Countries with >1000 sales

// Convert for view
return view('dashboard', [
    'geoData' => $geoData->toArray()
]);
```

---

### ReferralData

**Purpose:** Standardized referral/traffic source response

**Properties:**
- `Collection $data` - Collection of referrer data
- `?float $total` - Total count across all referrers
- `?array $percentages` - Percentage distribution
- `?array $categories` - Category distribution
- `?string $referrerField` - Referrer field name

**Methods:**
- `fromArray(array $data): self` - Create from array
- `toArray(): array` - Convert to array
- `toJson(): string` - Convert to JSON
- `count(): int` - Number of referrers
- `top(int $limit): Collection` - Get top N referrers
- `above(int $threshold): Collection` - Referrers above threshold
- `findByReferrer(string $referrer): mixed` - Find specific referrer
- `referrers(): array` - All referrer names
- `counts(): array` - All counts
- `sum(): int` - Total count
- `average(): float` - Average count
- `highest(): mixed` - Referrer with highest count
- `lowest(): mixed` - Referrer with lowest count
- `byCategory(string $category): Collection` - Get referrers by category
- `social(): Collection` - Social media referrers
- `searchEngines(): Collection` - Search engine referrers
- `email(): Collection` - Email referrers
- `direct(): Collection` - Direct traffic
- `other(): Collection` - Other/unknown referrers
- `categoryDistribution(): array` - Distribution by category
- `hasReferrer(string $referrer): bool` - Check if referrer exists
- `search(string $term): Collection` - Search referrers by term

**Example:**
```php
use App\Services\Statistics\DTOs\ReferralData;

$referralData = $stats->forModel(ProductView::class)
    ->referrals()->topReferrers($config);

echo $referralData->count();        // 50 referrers
echo $referralData->sum();          // Total views from all referrers

$topReferrers = $referralData->top(10);  // Top 10 referrers
$highest = $referralData->highest();      // Top referrer

// Category-specific data
$socialTraffic = $referralData->social();
$searchTraffic = $referralData->searchEngines();

// Category distribution
$distribution = $referralData->categoryDistribution();
// ['social' => ['count' => 1500, 'percentage' => 45.5], ...]

// Search referrers
$googleReferrers = $referralData->search('google');

// Convert for view
return view('analytics', [
    'referrals' => $referralData->toArray()
]);
```

---

## Complete Usage Examples

### Dashboard with All DTOs

```php
use App\Services\Statistics\DTOs\{
    ChartConfig,
    GeoDataConfig,
    ReferralConfig
};
use App\Services\Statistics\StatisticsService;

class DashboardController extends Controller
{
    public function index(StatisticsService $stats)
    {
        $startDate = request('start_date', '-30 days');
        $endDate = request('end_date', 'now');

        // Sales chart
        $salesChart = $stats->forModel(Order::class)
            ->dateRange($startDate, $endDate)
            ->where('status', 'completed')
            ->chart()->timeSeries(
                ChartConfig::timeSeries(
                    title: 'Daily Sales',
                    aggregation: 'sum',
                    aggregateField: 'total'
                )
            );

        // Geographical distribution
        $geoData = $stats->forModel(Order::class)
            ->dateRange($startDate, $endDate)
            ->geoData()->topLocations(
                GeoDataConfig::topLocations(
                    locationField: 'country',
                    limit: 10,
                    aggregation: 'sum',
                    aggregateField: 'total'
                )
            );

        // Traffic sources
        $referralData = $stats->forModel(ProductView::class)
            ->dateRange($startDate, $endDate)
            ->referrals()->trafficSources(
                ReferralConfig::trafficSources('referrer')
            );

        return view('dashboard', [
            'salesChart' => $salesChart->toArray(),
            'topCountries' => $geoData->top(5)->toArray(),
            'trafficDistribution' => $referralData->categoryDistribution(),
        ]);
    }
}
```

### API Response with DTOs

```php
use App\Services\Statistics\DTOs\ChartConfig;
use App\Services\Statistics\StatisticsService;

class StatisticsApiController extends Controller
{
    public function sales(StatisticsService $stats)
    {
        $config = ChartConfig::timeSeries(
            title: 'Sales Over Time',
            aggregation: 'sum',
            aggregateField: 'total'
        );

        $chartData = $stats->forModel(Order::class)
            ->dateRange('-30 days', 'now')
            ->where('status', 'completed')
            ->chart()->timeSeries($config);

        return response()->json([
            'success' => true,
            'data' => [
                'chart' => $chartData->toArray(),
                'summary' => [
                    'total' => $chartData->sum(),
                    'average' => $chartData->average(),
                    'count' => $chartData->count(),
                ],
            ],
        ]);
    }
}
```

### Testing with DTOs

```php
use App\Services\Statistics\DTOs\{ChartConfig, ChartData};
use Tests\TestCase;

class ChartGeneratorTest extends TestCase
{
    public function test_time_series_chart_with_dto()
    {
        $config = ChartConfig::timeSeries(
            title: 'Test Chart',
            aggregation: 'count'
        );

        $chartData = $this->stats
            ->forModel(Product::class)
            ->dateRange('-7 days', 'now')
            ->chart()->timeSeries($config);

        $this->assertInstanceOf(ChartData::class, $chartData);
        $this->assertEquals('Test Chart', $chartData->title);
        $this->assertCount(7, $chartData->labels);
        $this->assertIsArray($chartData->data);
    }
}
```

---

## DTO Summary

| DTO | Type | Purpose | Lines |
|-----|------|---------|-------|
| ChartConfig | Config | Chart configuration | ~150 |
| GeoDataConfig | Config | Geo data configuration | ~200 |
| ReferralConfig | Config | Referral configuration | ~230 |
| ChartData | Response | Chart response | ~110 |
| CounterData | Response | Counter response | ~130 |
| GeoData | Response | Geo data response | ~200 |
| ReferralData | Response | Referral response | ~320 |

**Total:** 7 DTOs, ~1,340 lines of type-safe code

---

## Benefits Summary

✅ **Type Safety** - All properties strongly typed
✅ **Validation** - Built-in validation for all configs
✅ **IDE Support** - Full autocompletion
✅ **Factory Methods** - Pre-configured common patterns
✅ **Fluent Interface** - Chainable configuration
✅ **Helper Methods** - Convenient data access
✅ **Testing** - Easier to mock and test
✅ **Documentation** - Self-documenting code
