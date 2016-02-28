<?php namespace Crip\Core\Traits;

use Crip\Core\Helpers\Slug;

/**
 * Class Slugable
 * @package Crip\UserManager\Traits
 */
trait Slugable
{

    /**
     * Set slug attribute to model.
     *
     * @param string $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Slug::make($value, config('roles.separator'));
    }

}