<?php namespace Crip\Core\Contracts;

use Crip\Core\Data\Model;

/**
 * Interface IPaginateRepository
 * @package Crip\Core\Contracts
 */
interface IPaginateRepository
{

    /**
     * Allowed pagination filters array
     *
     * @return array
     */
    public function paginateFilters();

    /**
     * @param Model $model
     * @param int $per_page
     * @param array $filters
     * @param array $columns
     * @return array
     */
    public function paginate(Model $model, $per_page = 15, array $filters = [], array $columns = ['*']);

}