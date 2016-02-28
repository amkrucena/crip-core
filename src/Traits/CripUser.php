<?php namespace Crip\Core\Traits;

/**
 * Class CripUser
 * @package Crip\Core\Traits
 */
trait CripUser
{

    /**
     * Always crypt password when setting it to model
     *
     * @param $val
     */
    public function setPasswordAttribute($val)
    {
        $this->attributes['password'] = bcrypt($val);
    }

}