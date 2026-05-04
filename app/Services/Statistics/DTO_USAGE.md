# Statistics Service - DTOs & Contracts Usage

This document demonstrates how to use the Statistics Service with DTOs (Data Transfer Objects) for type safety and better IDE support.

## Table of Contents
- [Why Use DTOs?](#why-use-dtos)
- [Chart Configuration](#chart-configuration)
- [Response Objects](#response-objects)
- [Backward Compatibility](#backward-compatibility)
- [Advanced Examples](#advanced-examples)

---

## Why Use DTOs?

DTOs provide several benefits:

1. **Type Safety**: Catch errors at compile time
2. **IDE Support**: Full autocompletion and hints
3. **Validation**: Built-in validation for required fields
4. **Self-Documenting**: Method signatures explain usage
5. **Refactoring**: Easier to find and update usages

---

## Chart Configuration

### Using ChartConfig DTO

**Before (Array-based):**
```php
$chart = $stats->forModel(Product::class)
    ->dateRange($startDate, $endDate)
    ->chart('timeSeries', [
        'title' => 'Product Sales',
        'aggregation' => 'count',
        'dateField' => 'created_at',
        'dateFormat' => 'd M'
    ]);
```

**After (DTO-based):**
```php
use App\Services\Statistics\DTOs\ChartConfig;

$config = ChartConfig::timeSeries(
    title: 'Product Sales',
    dateField: 'created_at',
    aggregation: 'count'
);

$chart = $stats->forModel(Product::class)
    ->dateRange($startDate, $endDate)
    ->chart()->timeSeries($config);
```

### Factory Methods

#### Time Series Chart
```php
$config = ChartConfig::timeSeries(
    title: 'Sales Over Time',
    dateField: 'created_at',
    aggregation: 'sum',
    aggregateField: 'price'
);

// Returns ChartData DTO
$chartData = $stats->forModel(Order::class)
    ->dateRange('-30 days', 'now')
    ->chart()->timeSeries($config);

// Access typed properties
echo $chartData->title;     // "Sales Over Time"
echo $chartData->max;       // Maximum value for scaling
echo $chartData->count();   // Number of data points
echo $chartData->sum();     // Total of all values
```

#### Bar Chart
```php
$config = ChartConfig::bar(
    title: 'Top Products',
    groupBy: 'product_id',
    aggregation: 'sum',
    aggregateField: 'quantity',
    limit: 10
);

$chartData = $stats->forModel(OrderItem::class)
    ->dateRange('-7 days', 'now')
    ->chart()->barChart($config);

// Access data
$labels = $chartData->labels;  // Product IDs
$data = $chartData->data;      // Quantities
```

#### Pie Chart
```php
$config = ChartConfig::pie(
    title: 'Sales by Category',
    groupBy: 'category_id',
    aggregation: 'sum',
    aggregateField: 'total'
);

$chartData = $stats->forModel(Order::class)
    ->chart()->pieChart($config);

// Percentages are automatically calculated
foreach ($chartData->percentages as $index => $percentage) {
    echo "{$chartData->labels[$index]}: {$percentage}%";
}
```

### Custom Configuration

For advanced use cases, you can create custom configurations:

```php
$config = new ChartConfig([
    'type' => 'timeSeries',
    'title' => 'Custom Chart',
    'dateField' => 'published_at',
    'aggregation' => 'avg',
    'aggregateField' => 'rating',
    'dateFormat' => 'M Y',
]);

// Validation is automatic
$config->validate(); // Throws exception if invalid

$chartData = $stats->forModel(Review::class)
    ->dateRange('-90 days', 'now')
    ->chart()->timeSeries($config);
```

---

## Response Objects

### ChartData DTO

The `ChartData` DTO provides structured access to chart results:

```php
$chartData = $stats->forModel(Product::class)
    ->dateRange('-30 days', 'now')
    ->chart()->timeSeries(
        ChartConfig::timeSeries('Product Views')
    );

// Typed properties
$chartData->title;        // string
$chartData->labels;       // array
$chartData->data;         // array
$chartData->max;          // ?int
$chartData->percentages;  // ?array
$chartData->type;         // ?string

// Helper methods
$chartData->count();      // Number of data points
$chartData->sum();        // Total sum
$chartData->average();    // Average value

// Convert to array/JSON
$array = $chartData->toArray();
$json = $chartData->toJson();
```

### CounterData DTO

For individual counter results:

```php
use App\Services\Statistics\DTOs\CounterData;

$counter = new CounterData(
    name: 'total_sales',
    value: 15000,
    label: 'Total Sales',
    icon: 'fas fa-dollar-sign',
    color: 'success',
    percentage: 25.5,
    growth: 12.3
);

// Access properties
echo $counter->formatted();         // "15K"
echo $counter->trend();             // "up", "down", or "stable"
echo $counter->isPositiveGrowth();  // true
```

---

## Backward Compatibility

Both arrays and DTOs are supported:

### Arrays (Still Supported)
```php
// Old way still works
$chart = $stats->forModel(Product::class)
    ->dateRange('-30 days', 'now')
    ->chart('timeSeries', [
        'title' => 'Product Sales',
        'aggregation' => 'count'
    ]);

// Returns array
var_dump($chart); // ['title' => '...', 'labels' => [...], ...]
```

### DTOs (Recommended)
```php
// New way with DTOs
$config = ChartConfig::timeSeries('Product Sales');

$chartData = $stats->forModel(Product::class)
    ->dateRange('-30 days', 'now')
    ->chart()->timeSeries($config);

// Returns ChartData DTO
echo $chartData->title;
echo $chartData->count();
```

### Migration Strategy

1. **Continue using arrays** for existing code (no breaking changes)
2. **Use DTOs** for new features (better type safety)
3. **Gradually refactor** old code when making changes

---

## Advanced Examples

### Dashboard with Multiple Charts

```php
use App\Services\Statistics\DTOs\ChartConfig;
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

        // Products chart
        $productsChart = $stats->forModel(Product::class)
            ->dateRange($startDate, $endDate)
            ->chart()->barChart(
                ChartConfig::bar(
                    title: 'Top Products',
                    groupBy: 'name',
                    aggregation: 'count',
                    limit: 10
                )
            );

        // Category distribution
        $categoryPie = $stats->forModel(Order::class)
            ->chart()->pieChart(
                ChartConfig::pie(
                    title: 'Sales by Category',
                    groupBy: 'category_id',
                    aggregation: 'sum',
                    aggregateField: 'total'
                )
            );

        return view('dashboard', [
            'salesChart' => $salesChart->toArray(),
            'productsChart' => $productsChart->toArray(),
            'categoryPie' => $categoryPie->toArray(),
        ]);
    }
}
```

### Custom Validation

```php
use App\Services\Statistics\DTOs\ChartConfig;

try {
    $config = new ChartConfig([
        'type' => 'timeSeries',
        'title' => 'Sales',
        // Missing required 'dateField'
    ]);

    $config->validate();
} catch (\InvalidArgumentException $e) {
    // Handle validation error
    logger()->error('Chart config validation failed: ' . $e->getMessage());
}
```

### Reusable Configurations

```php
class ChartConfigFactory
{
    public static function salesOverTime(string $dateField = 'created_at'): ChartConfig
    {
        return ChartConfig::timeSeries(
            title: 'Sales Over Time',
            dateField: $dateField,
            aggregation: 'sum',
            aggregateField: 'total'
        );
    }

    public static function topSellingProducts(int $limit = 10): ChartConfig
    {
        return ChartConfig::bar(
            title: 'Top Selling Products',
            groupBy: 'product_name',
            aggregation: 'sum',
            aggregateField: 'quantity',
            limit: $limit
        );
    }
}

// Usage
$chart = $stats->forModel(Order::class)
    ->dateRange('-30 days', 'now')
    ->chart()->timeSeries(
        ChartConfigFactory::salesOverTime()
    );
```

### Testing with DTOs

```php
use App\Services\Statistics\DTOs\ChartConfig;
use App\Services\Statistics\DTOs\ChartData;
use Tests\TestCase;

class ChartGeneratorTest extends TestCase
{
    public function test_time_series_chart_generation()
    {
        $config = ChartConfig::timeSeries(
            title: 'Test Chart',
            dateField: 'created_at',
            aggregation: 'count'
        );

        $chartData = $this->stats->forModel(Product::class)
            ->dateRange('-7 days', 'now')
            ->chart()->timeSeries($config);

        $this->assertInstanceOf(ChartData::class, $chartData);
        $this->assertEquals('Test Chart', $chartData->title);
        $this->assertIsArray($chartData->labels);
        $this->assertIsArray($chartData->data);
        $this->assertCount(7, $chartData->labels);
    }
}
```

---

## Best Practices

1. **Use DTOs for new code**: Better type safety and IDE support
2. **Factory methods**: Use `ChartConfig::timeSeries()` instead of manual construction
3. **Validate early**: Call `$config->validate()` before passing to generators
4. **Type hint returns**: Specify `ChartData` return types in methods
5. **Convert to arrays**: Use `->toArray()` when passing to views
6. **Document configurations**: Create factory classes for reusable configs
7. **Test with DTOs**: Write tests using DTOs for better type checking

---

## Migration Checklist

- [ ] Identify code using array-based chart configuration
- [ ] Import DTO classes (`use App\Services\Statistics\DTOs\ChartConfig`)
- [ ] Replace array config with factory methods
- [ ] Update return type hints to `ChartData`
- [ ] Update tests to use DTOs
- [ ] Run tests to verify functionality
- [ ] Update documentation

---

## Reference

### ChartConfig Factory Methods

- `ChartConfig::timeSeries($title, $dateField, $aggregation, $aggregateField)`
- `ChartConfig::bar($title, $groupBy, $aggregation, $aggregateField, $limit)`
- `ChartConfig::pie($title, $groupBy, $aggregation, $aggregateField)`

### ChartData Methods

- `count()` - Number of data points
- `sum()` - Total sum of values
- `average()` - Average value
- `toArray()` - Convert to array
- `toJson()` - Convert to JSON string

### CounterData Methods

- `formatted()` - Format value with K/M suffix
- `trend()` - Get growth trend (up/down/stable)
- `isPositiveGrowth()` - Check if growth is positive
- `isNegativeGrowth()` - Check if growth is negative
- `toArray()` - Convert to array
