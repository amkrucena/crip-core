<?php namespace Crip\Core\Data;

use Input;

/**
 * Class InputService
 * @package Crip\Core\Data
 */
class InputService
{
    /**
     * @param      $key
     * @param null $default
     * @param bool $deep
     *
     * @return mixed
     */
    public function get($key, $default = null, $deep = false)
    {
        return Input::get($key, $default, $deep);
    }

    /**
     * @param string $key
     * @param null $default
     *
     * @return array
     */
    public function decode($key, $default = null)
    {
        $input = $this->get($key, $default);
        if ($input AND is_string($input)) {
            $input = json_decode($input, true);
        }

        return (array)$input;
    }
}