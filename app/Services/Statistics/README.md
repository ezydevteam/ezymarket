# Statistics Service - Usage Guide

## Overview
The `StatisticsService` provides a fluent, reusable interface for generating various types of analytics data including counters, charts, geographical statistics, and referral data.

## Basic Usage

### 1. Simple Counters

```php
use App\Services\Statistics\StatisticsService;
use App\Models\Sale;

$stats = app(StatisticsService::class)
    ->forModel(Sale::class)
    ->where('product_id', $productId)
    ->dateRange($startDate, $endDate);

$counters = $stats->counters([
    'total_sales' => ['count', '*'],
    'total_revenue' => ['sum', 'price'],
    'avg_order_value' => ['avg', 'price'],
]);

// Result: ['total_sales' => 150, 'total_revenue' => 25000, 'avg_order_value' => 166.67]
```

### 2. Time Series Charts

```php
$salesChart = $stats->chart('timeSeries', [
    'title' => 'Sales Over Time',
    'dateField' => 'created_at',
    'aggregation' => 'count',
]);

// Result: ['title' => 'Sales Over Time', 'labels' => [...], 'data' => [...], 'max' => 120]
```

### 3. Bar Charts

```php
$topProducts = $stats
    ->forModel(Sale::class)
    ->chart('bar', [
        'title' => 'Top Products',
        'groupBy' => 'product_id',
        'aggregation' => 'sum',
        'aggregateField' => 'price',
        'limit' => 10,
    ]);
```

### 4. Geographical Data

```php
// Top countries by revenue
$topCountries = $stats->geoData('byCountry', [
    'aggregation' => 'sum',
    'field' => 'price',
    'limit' => 10,
]);

// All countries with sales count
$allCountries = $stats->geoData('allCountries');
```

### 5. Referral Statistics

```php
$referrals = $stats
    ->forModel(ProductView::class)
    ->referrals([
        'field' => 'referrer',
        'limit' => 10,
    ]);

// Traffic source categorization
$trafficSources = $stats->trafficSources('referrer');
```

## Advanced Examples

### Dashboard Statistics for Sellers

```php
public function sellerDashboard()
{
    $sellerId = authUser()->id;
    $startDate = Date::now()->startOfMonth();
    $endDate = Date::now()->endOfMonth();

    $stats = app(StatisticsService::class)
        ->forModel(Sale::class)
        ->where('seller_id', $sellerId)
        ->scope('active')
        ->dateRange($startDate, $endDate);

    $counters = $stats->counters([
        'total_orders' => ['count', '*'],
        'total_revenue' => ['sum', 'seller_earning'],
        'avg_order_value' => ['avg', 'price'],
    ]);

    $salesChart = $stats->chart('timeSeries', [
        'title' => 'Your Sales',
        'aggregation' => 'sum',
        'aggregateField' => 'seller_earning',
    ]);

    return view('dashboard', compact('counters', 'salesChart'));
}
```

### Product Performance Comparison

```php
public function compareProducts($productId1, $productId2)
{
    $stats1 = app(StatisticsService::class)
        ->forModel(Sale::class)
        ->where('product_id', $productId1)
        ->dateRange($startDate, $endDate);

    $stats2 = app(StatisticsService::class)
        ->forModel(Sale::class)
        ->where('product_id', $productId2)
        ->dateRange($startDate, $endDate);

    $comparison = [
        'product1' => $stats1->counters([
            'sales' => ['count', '*'],
            'revenue' => ['sum', 'price'],
        ]),
        'product2' => $stats2->counters([
            'sales' => ['count', '*'],
            'revenue' => ['sum', 'price'],
        ]),
    ];

    return view('comparison', compact('comparison'));
}
```

### User Activity Statistics

```php
public function userActivity($userId)
{
    $stats = app(StatisticsService::class)
        ->forModel(User::class, $userId);

    // Get user's purchases
    $purchases = $stats
        ->forModel(Sale::class)
        ->where('user_id', $userId)
        ->counters([
            'total_purchases' => ['count', '*'],
            'total_spent' => ['sum', 'price'],
        ]);

    // Get user's views
    $views = $stats
        ->forModel(ProductView::class)
        ->where('user_id', $userId)
        ->counters(['total_views' => ['count', '*']]);

    return compact('purchases', 'views');
}
```

## Available Methods

### Query Building
- `forModel(string $class, $id = null)` - Set the model
- `where(string $field, $operator, $value)` - Add where condition
- `whereIn(string $field, array $values)` - Add whereIn condition
- `whereNotNull(string $field)` - Add whereNotNull condition
- `dateRange(Carbon $start, Carbon $end)` - Set date range
- `with(array $relations)` - Eager load relationships
- `scope(string $name, ...$params)` - Apply model scope

### Counter Aggregations
- `count` - Count records
- `sum` - Sum field values
- `avg` or `average` - Average field values
- `min` or `minimum` - Minimum value
- `max` or `maximum` - Maximum value

### Chart Types
- `timeSeries` or `line` - Time-based line chart
- `bar` - Bar chart
- `pie` - Pie chart with percentages

### Geo Data Types
- `byCountry` - Group by country
- `topLocations` - Top locations by metric
- `distribution` - Distribution map data
- `allCountries` - All countries list

## Migration Path

### Before (Old Way)
```php
private function generateSalesChartData($product, $startDate, $endDate)
{
    $chart['title'] = translate('Sales');
    $dates = chartDates($startDate, $endDate);

    $sales = Sale::active()
        ->where('product_id', $product->id)
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
        ->groupBy('date')
        ->pluck('count', 'date');

    // ... 20 more lines of code
}
```

### After (New Way)
```php
$salesChart = app(StatisticsService::class)
    ->forModel(Sale::class)
    ->where('product_id', $product->id)
    ->scope('active')
    ->dateRange($startDate, $endDate)
    ->chart('timeSeries', [
        'title' => 'Sales',
        'aggregation' => 'count',
    ]);
```

## Benefits

✅ **DRY Code** - No more duplicated statistics logic
✅ **Reusable** - Use across any controller or service
✅ **Testable** - Easy to mock and test
✅ **Flexible** - Supports any model and aggregation
✅ **Maintainable** - Changes in one place
✅ **Type-Safe** - Full IDE autocompletion support
