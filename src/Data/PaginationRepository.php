<?php namespace Crip\Core\Data;

use Crip\Core\Contracts\IPaginateRepository;

/**
 * Class PaginationRepository
 * @package Crip\Core\Data
 */
abstract class PaginationRepository extends Repository implements IPaginateRepository
{

    /**
     * Allowed pagination filters array
     *
     * @return array
     */
    abstract function paginateFilters();

    /**
     * Eager relations can be taken from request input
     * Filters taken from request
     *
     * @param Model $model
     * @param int $per_page
     * @param array $filters
     * @param array $columns
     * @return array
     */
    public function paginate(Model $model, $per_page = 15, array $filters = [], array $columns = ['*'])
    {
        $this->onlyTrashed($model);
        $this->model = $this->relation->apply($this->model, $this);
        $this->model = $this->filter->apply($this->model, $this, $filters);

        return $this->model->order()->paginate($per_page, $columns)->toArray();
    }

}