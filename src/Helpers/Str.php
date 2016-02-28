<?php namespace Crip\Core\Helpers;

/**
 * Class Str
 * @package Crip\Core\Helpers
 */
class Str
{

    /**
     * Converts string from special characters to URL friendly string
     *
     * @param string $string String co covert
     * @param string $separator Separator to replace spaces
     * @param null $emptyValue Return value if target result is empty
     *
     * @return string URL friendly string
     */
    public static function slug($string, $separator = '-', $emptyValue = null)
    {
        return Slug::make($string, $separator, $emptyValue);
    }

    /**
     * Split int|string|array to array
     *
     * @param int|string|array $string
     * @param string $split
     *
     * @return array
     */
    public static function toArray($string, $split = '/ ?[,|] ?/')
    {
        return (!is_array($string)) ? preg_split('/ ?[,|] ?/', $string) : $string;
    }
}