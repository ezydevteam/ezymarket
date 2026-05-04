<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * SortableScope
 *
 * Global scope that automatically orders models by their sort_id column.
 * Used for models that support custom sorting/ordering.
 */
class SortableScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $builder->orderBy('sort_id', 'asc');
    }
}


















