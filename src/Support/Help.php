<?php namespace Crip\Core\Support;

use Crip\Core\Contracts\ICripObject;
use Crip\UserManager\App\Services\UserService;

/**
 * Class Help
 * @package Crip\Core\Support
 */
class Help implements ICripObject
{
    /**
     * Determine is $class_name instance of $target
     *
     * @param $target
     * @param $class_name
     * @return bool
     */
    public static function isInstanceOf($class_name, $target)
    {
        return is_a($target, $class_name);
    }
}