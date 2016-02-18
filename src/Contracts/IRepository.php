<?php namespace Crip\Core\Contracts;

use Crip\Core\Data\Model;
use Crip\Core\Exceptions\BadDataQueryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface IRepository
 * @package Crip\Core\Contracts
 */
interface IRepository
{
    /**
     * Retrieve model name
     *
     * @return string
     */
    public function model();

    /**
     * Allowed repo relations array
     *
     * @return array
     */
    public function relations();

    /**
     * Get current repository model
     *
     * @return Builder
     */
    public function getModelBuilder();

    /**
     * Indicates if the model exists.
     *
     * @return bool
     */
    public function exists();

    /**
     * @return Model
     */
    public function getModel();

    /**
     * Get the table associated with the repository model.
     *
     * @return string
     */
    public function getTable();

    /**
     * @param bool $overwrite_existing
     * @return Builder
     * @throws BadDataQueryException
     */
    public function makeModel($overwrite_existing = false);

    /**
     * @param        $find
     * @param array $with
     * @param string $column
     * @param array $columns
     * @return Model
     */
    public function findWith($find, array $with = [], $column = 'id', array $columns = ['*']);

    /**
     * Find single record with secure relation keys only
     *
     * @param $find
     * @param string $column
     * @param array $relations
     * @param array $columns
     *
     * @return Model
     */
    public function find($find, $column = 'id', array $relations = [], array $columns = ['*']);

    /**
     * @param        $slug
     * @param array $columns
     * @param string $column
     * @return Model
     */
    public function findBySlug($slug, array $columns = ['*'], $column = 'slug');

    /**
     * @param $find
     * @param string $column
     * @param string $where_role
     * @param array $columns
     * @return Collection
     */
    public function get($find, $column = 'id', $where_role = '=', array $columns = ['*']);

    /**
     * @param $find
     * @param string $column
     * @param string $where_role
     * @param array $with
     * @param array $columns
     * @return Collection
     */
    public function getWith($find, $column = 'id', $where_role = '=', array $with = [], array $columns = ['*']);

    /**
     * @param $find
     * @param string $column
     * @param string $where_role
     * @param string $order_by
     * @param bool $ascending
     * @param array $with
     * @param array $columns
     * @return Collection
     */
    public function getOrdered(
        $find,
        $column = 'id',
        $where_role = '=',
        $order_by = 'id',
        $ascending = true,
        array $with = [],
        array $columns = ['*']
    );

    /**
     * @param       $value
     * @param array $in
     * @return Model
     */
    public function searchFor($value, array $in);

    /**
     * @param array $attributes
     * @return bool
     * @throws BadDataQueryException
     */
    public function canFind(array $attributes);

    /**
     * @param array $input
     *
     * @return Model
     */
    public function create(array $input);

    /**
     * @param array $input
     * @param int $id
     * @param string $attribute
     *
     * @return Model
     */
    public function update(array $input, $id, $attribute = 'id');

    /**
     * @param int $id
     *
     * @return boolean
     */
    public function delete($id);

    /**
     * @param int $id
     *
     * @return mixed
     * @throws BadDataQueryException
     */
    public function restore($id);

    /**
     * @param array $ids
     * @return Collection
     */
    public function searchIds(array $ids);
}