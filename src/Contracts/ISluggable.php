<?php namespace Crip\Core\Contracts;

/**
 * Interface ISluggable
 * @package Crip\Core\Contracts
 */
interface ISluggable
{
    public function getSlug();

    public function sluggify($force = false);

    public function resluggify();
}