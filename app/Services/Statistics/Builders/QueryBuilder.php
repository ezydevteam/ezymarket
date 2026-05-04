<?php

namespace App\Services\Statistics\Builders;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * QueryBuilder
 *
 * Fluent interface for building statistics queries with filters,
 * date ranges, and various conditions.
 */
class QueryBuilder
{
    protected Builder $query;
    protected ?string $modelClass = null;
    protected $modelId = null;
    protected array $filters = [];
    protected ?Carbon $startDate = null;
    protected ?Carbon $endDate = null;
    protected string $dateField = 'created_at';

    /**
     * Set the model class and optional ID for the query
     *
     * @param string $modelClass
     * @param mixed $modelId
     * @return self
     */
    public function forModel(string $modelClass, $modelId = null): self
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->query = $modelClass::query();

        if ($modelId) {
            $this->query->where((new $modelClass)->getKeyName(), $modelId);
        }

        return $this;
    }

    /**
     * Add a where condition to the query
     *
     * @param string $field
     * @param mixed $operator
     * @param mixed $value
     * @return self
     */
    public function where(string $field, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->filters[] = ['type' => 'where', 'field' => $field, 'operator' => $operator, 'value' => $value];
        $this->query->where($field, $operator, $value);

        return $this;
    }

    /**
     * Add a whereIn condition to the query
     *
     * @param string $field
     * @param array $values
     * @return self
     */
    public function whereIn(string $field, array $values): self
    {
        $this->filters[] = ['type' => 'whereIn', 'field' => $field, 'values' => $values];
        $this->query->whereIn($field, $values);

        return $this;
    }

    /**
     * Add a whereNotNull condition to the query
     *
     * @param string $field
     * @return self
     */
    public function whereNotNull(string $field): self
    {
        $this->filters[] = ['type' => 'whereNotNull', 'field' => $field];
        $this->query->whereNotNull($field);

        return $this;
    }

    /**
     * Add a whereHas condition to the query
     *
     * @param string $relation
     * @param Closure|null $callback
     * @param string $operator
     * @param int $count
     * @return self
     */
    public function whereHas(string $relation, Closure $callback = null, string $operator = '>=', int $count = 1): self
    {
        $this->filters[] = ['type' => 'whereHas', 'relation' => $relation, 'callback' => $callback];
        $this->query->whereHas($relation, $callback, $operator, $count);

        return $this;
    }

    /**
     * Add a whereDoesntHave condition to the query
     *
     * @param string $relation
     * @param Closure|null $callback
     * @return self
     */
    public function whereDoesntHave(string $relation, Closure $callback = null): self
    {
        $this->filters[] = ['type' => 'whereDoesntHave', 'relation' => $relation, 'callback' => $callback];
        $this->query->whereDoesntHave($relation, $callback);

        return $this;
    }

    /**
     * Set the date range for the query
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dateField
     * @return self
     */
    public function dateRange(Carbon $startDate, Carbon $endDate, string $dateField = 'created_at'): self
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->dateField = $dateField;

        $this->query->where($dateField, '>=', $startDate)
                    ->where($dateField, '<=', $endDate);

        return $this;
    }

    /**
     * Add eager loading relationships
     *
     * @param array $relations
     * @return self
     */
    public function withRelations(array $relations): self
    {
        $this->query->with($relations);
        return $this;
    }

    /**
     * Add a scope to the query (e.g., 'active', 'published')
     *
     * @param string $scope
     * @param mixed ...$parameters
     * @return self
     */
    public function withScope(string $scope, ...$parameters): self
    {
        $this->query->{$scope}(...$parameters);
        return $this;
    }

    /**
     * Get the underlying Eloquent query builder
     *
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get the model class
     *
     * @return string|null
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the start date
     *
     * @return Carbon|null
     */
    public function getStartDate(): ?Carbon
    {
        return $this->startDate;
    }

    /**
     * Get the end date
     *
     * @return Carbon|null
     */
    public function getEndDate(): ?Carbon
    {
        return $this->endDate;
    }

    /**
     * Get the date field
     *
     * @return string
     */
    public function getDateField(): string
    {
        return $this->dateField;
    }

    /**
     * Clone the query builder for reuse
     *
     * @return self
     */
    public function clone(): self
    {
        $cloned = new self();
        $cloned->query = clone $this->query;
        $cloned->modelClass = $this->modelClass;
        $cloned->modelId = $this->modelId;
        $cloned->filters = $this->filters;
        $cloned->startDate = $this->startDate;
        $cloned->endDate = $this->endDate;
        $cloned->dateField = $this->dateField;

        return $cloned;
    }
}
