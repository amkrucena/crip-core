<?php namespace Crip\Core\Data;

use Crip\Core\Contracts\ICripObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Input;

/**
 * Class Model
 * @package Crip\Core\Data
 */
class Model extends EloquentModel implements ICripObject
{

    /**
     * Scope a query to order by query properties
     *
     * @param $query
     * @return Builder
     */
    public function scopeOrder($query)
    {
        $order = Input::get('order', isset($this->order) ? $this->order : 'id') ?: 'id';
        $direction = Input::get('direction', isset($this->direction) ? $this->direction : 'desc') ?: 'desc';

        return $query->orderBy($order, $direction);
    }

}