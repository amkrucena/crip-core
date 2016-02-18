<?php namespace Crip\Core\Data;

use Crip\Core\Contracts\ICripObject;
use Illuminate\Database\Eloquent\Builder;
use Input;

/**
 * Class RelationInputService
 * @package Crip\Core\Data
 */
class RelationInputService implements ICripObject
{

    /**
     * @var InputService
     */
    private $inputService;

    /**
     * @param InputService $inputService
     */
    public function __construct(InputService $inputService)
    {
        $this->inputService = $inputService;
    }

    /**
     * Apply eager load relations to query builder
     *
     * @param Builder $builder
     * @param Repository $repository
     * @param array $relations
     * @return Builder
     */
    public function apply(Builder $builder, Repository $repository, array $relations = [])
    {
        $relations = $this->getRelations($repository, $relations);

        return $builder->with($relations);
    }

    /**
     * Get relation array
     *
     * @param Repository $repository
     * @param array $relations
     * @return array
     */
    private function getRelations(Repository $repository, array $relations = [])
    {
        // if we have relation array passed, ignore request input
        if (count($relations) === 0) {
            $relations = Input::get('with');

            if (!is_array($relations)) {
                $relations = $this->inputService->decode('with');
            }
        }

        $result = [];
        $repo_relations = $repository->relations();
        foreach ($relations as $key => $value) {
            if (in_array($key, $repo_relations)) {
                $result[] = $key;
            } elseif (in_array($value, $repo_relations)) {
                $result[] = $value;
            }
        }

        return $result;
    }
}