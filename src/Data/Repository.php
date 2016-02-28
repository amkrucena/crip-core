<?php namespace Crip\Core\Data;

use Crip\Core\Contracts\ICripObject;
use Crip\Core\Contracts\IRepository;
use Crip\Core\Contracts\ISluggable;
use Crip\Core\Exceptions\BadDataQueryException;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Crip\Core\Data\Model;
use Input;

/**
 * Class Repository
 * @package Crip\Core\Data
 */
abstract class Repository implements IRepository, ICripObject
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var FilterInputService
     */
    protected $filter;

    /**
     * @var RelationInputService
     */
    protected $relation;

    /**
     * @var Builder
     */
    protected $model = null;

    /**
     * @var Model
     */
    protected $modelInstance;

    /**
     * @var bool
     */
    protected $isSluggable = false;

    /**
     * Avoid field update, if it is empty
     *
     * @var array
     */
    protected $avoidEmptyUpdate = ['password'];

    /**
     * @param App $app
     * @param IFiltersInputService $filter
     * @param IRelationsInputService $relation
     *
     * @throws RepositoryException
     */
    public function __construct(App $app, FilterInputService $filter, RelationInputService $relation)
    {
        $this->app = $app;
        $this->filter = $filter;
        $this->relation = $relation;
        $this->makeModel(true);
    }

    /**
     * Retrieve model name
     *
     * @return string
     */
    abstract public function model();

    /**
     * Allowed repo relations array
     *
     * @return array
     */
    abstract public function relations();

    /**
     * Get current repository model
     *
     * @return Builder
     */
    public function getModelBuilder()
    {
        return $this->model;
    }

    /**
     * Indicates if the model exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->modelInstance->exists;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->modelInstance;
    }

    /**
     * Get the table associated with the repository model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->modelInstance->getTable();
    }

    /**
     * @param bool $overwrite_existing
     * @return Builder
     * @throws BadDataQueryException
     */
    public function makeModel($overwrite_existing = false)
    {
        if ($overwrite_existing || $this->model === null) {
            $this->modelInstance = $this->app->make($this->model());
            if (!$this->modelInstance instanceof Model) {
                $message = 'Class `%s` must be an instance of `%s`';
                $message = sprintf($message, $this->model(), Model::class);

                throw new BadDataQueryException($this, $message);
            }

            if ($this->modelInstance instanceof ISluggable) {
                $this->isSluggable = true;
            }

            $this->model = $this->modelInstance->newQuery();
        }

        return $this->model;
    }

    /**
     * Find single record with secure relation keys only
     * Eager relations can be taken from request input
     *
     * @param $find
     * @param string $column
     * @param array $relations
     * @param array $columns
     * @return Model
     */
    public function find($find, $column = 'id', array $relations = [], array $columns = ['*'])
    {
        $this->model = $this->where($column, $find);
        $this->model = $this->relation->apply($this->model, $this, $relations);

        return $this->model->firstOrFail($columns);
    }

    /**
     * Find single record with custom relation query
     *
     * @param        $find
     * @param array $with
     * @param string $column
     * @param array $columns
     * @return Model
     */
    public function findWith($find, array $with = [], $column = 'id', array $columns = ['*'])
    {
        return $this->whereWith($find, $column, $with)->firstOrFail($columns);
    }

    /**
     * Find single record where slug column contains
     * Eager relations can be taken from request input
     *
     * @param        $slug
     * @param array $columns
     * @param string $column
     * @return Model
     */
    public function findBySlug($slug, array $columns = ['*'], $column = 'slug')
    {
        return $this->find($slug, $column, $columns);
    }

    /**
     * @param $find
     * @param string $column
     * @param string $where_role
     * @param array $columns
     * @return Collection
     */
    public function get($find, $column = 'id', $where_role = '=', array $columns = ['*'])
    {
        return $this->where($column, $find, $where_role)->get($columns);
    }

    /**
     * @param $find
     * @param string $column
     * @param string $where_role
     * @param array $with
     * @param array $columns
     * @return Collection
     */
    public function getWith($find, $column = 'id', $where_role = '=', array $with = [], array $columns = ['*'])
    {
        return $this->whereWith($find, $column, $with, $where_role)->get($columns);
    }

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
    ) {
        $order = $ascending ? 'ASC' : 'DESC';

        return $this->whereWith($find, $column, $with, $where_role)
            ->orderBy($order_by, $order)
            ->get($columns);
    }

    /**
     * @param       $value
     * @param array $in
     * @return Model
     */
    public function searchFor($value, array $in)
    {
        foreach ($in as $key) {
            $this->model = $this->model->orWhere($key, $value);
        }

        return $this->model->firstOrFail();
    }

    /**
     * @param array $attributes
     * @return bool
     * @throws BadDataQueryException
     */
    public function canFind(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->where($key, $value);
        }

        return $this->model->count() > 0;
    }

    /**
     * @param array $input
     *
     * @return Model
     */
    public function create(array $input)
    {
        $input = $this->onlyFillable($input);

        return $this->modelInstance->create($input);
    }

    /**
     * @param array $input
     * @param int $id
     * @param \Crip\Core\Data\Model $model
     * @param string $attribute
     *
     * @return \Crip\Core\Data\Model
     */
    public function update(array $input, $id, Model $model = null, $attribute = 'id')
    {
        if ($model == null) {
            $model = $this->find($id, $attribute);
        }

        $model->update($this->onlyFillable($input));

        return $model;
    }

    /**
     * @param int $id
     *
     * @return boolean
     */
    public function delete($id)
    {
        if ($this->hasDeleteTrait($this->modelInstance)) {
            return $this->destroy($id);
        }

        return $this->modelInstance->destroy($id);
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws BadDataQueryException
     */
    public function restore($id)
    {
        if ($this->hasDeleteTrait($this->modelInstance)) {
            $this->modelInstance = $this->modelInstance->withTrashed()->findOrFail($id);

            return $this->modelInstance->restore();
        }

        $message = 'Table `%s` do not contains deleted records';
        $message = sprintf($message, $this->getTable());

        throw new BadDataQueryException($this, $message);
    }

    /**
     * @param array $ids
     * @return Collection
     */
    public function searchIds(array $ids)
    {
        return $this->model->whereIn('id', $ids)->lists('id');
    }

    /**
     * Filter input from un allowed fields for model
     *
     * @param array $input
     * @return array
     */
    public function onlyFillable(array $input)
    {
        $result = array_intersect_key($input, array_flip($this->modelInstance->getFillable()));

        foreach ($this->avoidEmptyUpdate as $avoid) {
            if (array_key_exists($avoid, $result) && empty($result[$avoid])) {
                unset($result[$avoid]);
            }
        }

        return $result;
    }

    /**
     * @param $column
     * @param $value
     * @param string $where_role
     * @return Builder
     * @throws BadDataQueryException
     */
    protected function where($column, $value, $where_role = '=')
    {
        $this->makeModel();
        $this->model = $this->model->where($column, $where_role, $value);

        return $this->model;
    }

    /**
     * @param array $with
     * @return Builder
     * @throws BadDataQueryException
     */
    protected function with(array $with = [])
    {
        $this->makeModel();
        $this->model = $this->model->with($with);

        return $this->model;
    }

    /**
     * Unfiltered querable with this model
     *
     * @param $find
     * @param $column
     * @param $with
     * @param string $where_role
     * @return Builder
     */
    protected function whereWith($find, $column, $with, $where_role = '=')
    {
        $this->where($column, $where_role, $find);
        $this->with($with);

        return $this->model;
    }

    /**
     * @param Model $model
     * @return bool
     */
    protected function hasDeleteTrait(Model $model)
    {
        return method_exists($model, 'withTrashed');
    }

    /**
     * @param $id
     * @return bool|null
     * @throws \Exception
     */
    protected function destroy($id)
    {
        $this->modelInstance = $this->modelInstance->withTrashed()->findOrFail($id);

        if ($this->modelInstance->trashed()) {
            return $this->modelInstance->forceDelete();
        }

        return $this->modelInstance->delete();
    }

    /**
     * @param Model $model
     * @return Builder
     */
    protected function onlyTrashed(Model $model)
    {
        $this->makeModel();
        if (Input::get('trashed') === 'true') {
            if ($this->hasDeleteTrait($model)) {
                $this->model = $model->newQuery()->onlyTrashed();
            }
        }

        return $this->model;
    }
}