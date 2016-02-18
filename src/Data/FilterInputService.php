<?php namespace Crip\Core\Data;

use Crip\Core\Contracts\ICripObject;
use Crip\Core\Contracts\IPaginateRepository;
use Crip\Core\Exceptions\BadDataQueryException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class FilterInputService
 * @package Crip\Core\Data
 */
class FilterInputService implements ICripObject
{

    /**
     * Filter comparator allowed signs
     *
     * @var array
     */
    private $filter_comparators
        = [
            '=',
            '>',
            '<',
            'like',
        ];

    /**
     * @var array
     */
    private $wrap_in_percents
        = [
            'like'
        ];

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
        // TODO: $filter_comparators and $wrap_in_percents should be configurable
    }

    /**
     * @param Builder $model
     * @param IPaginateRepository $repository
     * @param array $filters
     *
     * @return Builder
     * @throws BadDataQueryException
     */
    public function apply(Builder $model, IPaginateRepository $repository, array $filters)
    {
        foreach ($this->inputService->decode('filters') as $filter) {
            $model = $this->applyFilter($filter, $model, $repository);
        }

        foreach ($filters as $filter) {
            $model = $this->addFilter($model, $filter);
        }

        return $model;
    }

    /**
     * @param array $filter
     * @param Builder $model
     * @param IPaginateRepository $repository
     *
     * @return Builder
     * @throws BadDataQueryException
     */
    private function applyFilter(array $filter, Builder $model, IPaginateRepository $repository)
    {
        if (isset($filter[0]) AND in_array($filter[0], (array)$repository->paginateFilters())) {
            $model = $this->addFilter($model, $filter);
        } else {
            $message = '`%s` repository filters can contain only %s columns';
            $message = sprintf($message, get_class($repository), join(', ', $repository->paginateFilters()));

            throw new BadDataQueryException($this, $message);
        }

        return $model;
    }

    /**
     * @param Builder $model
     * @param array $filter
     *
     * @return $this|Builder
     * @throws BadDataQueryException
     */
    private function addFilter(Builder $model, array $filter)
    {
        switch (count($filter)) {
            case 2:
                $model = $model->where($filter[0], $this->trim($filter[1]));
                break;
            case 3:
                if (!in_array($filter[1], $this->filter_comparators)) {
                    $message = 'Api filters can contain only %s comparators';
                    $message = sprintf($message, join(', ',$this->filter_comparators));

                    throw new BadDataQueryException($this, $message);
                }
                $model = $model->where($filter[0], $filter[1], $this->trim($filter[2], $filter[1]));
                break;
        }

        return $model;
    }

    /**
     * @param      $value
     * @param bool $comparator
     *
     * @return string
     */
    private function trim($value, $comparator = false)
    {
        $value = trim($value);
        if (in_array($comparator, $this->wrap_in_percents)) {
            return '%' . $value . '%';
        }

        return $value;
    }
}