<?php

namespace App\Services\Statistics\Contracts;

use App\Services\Statistics\Builders\QueryBuilder;

/**
 * GeneratorInterface
 *
 * Contract that all statistics generators must implement.
 * Ensures consistent behavior across different generator types.
 */
interface GeneratorInterface
{
    /**
     * Create a new generator instance
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder);
}
