<?php namespace Crip\Core\Middleware;

use Crip\Core\Contracts\ICripObject;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class CripCoreMiddleware
 * @package Crip\Core\Middleware
 */
class CripCoreMiddleware implements ICripObject
{
    /**
     * @var \Illuminate\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param \Illuminate\Contracts\Auth\Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

}