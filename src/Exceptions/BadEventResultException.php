<?php namespace Crip\Core\Exceptions;

use Config;
use Crip\Core\Contracts\ICripObject;
use Exception;

/**
 * Class BadEventResultException
 * @package Crip\Core\Exceptions
 */
class BadEventResultException extends Exception
{
    /**
     * @param ICripObject $object
     * @param array $events
     * @param string $expects
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(ICripObject $object, array $events, $expects, $code = 0, Exception $previous = null)
    {
        $message = 'In `%s` events [%s] expects to get in result instance of `%s`';
        $message = sprintf($message, get_class($object), join(', ', $events), $expects);

        parent::__construct($message, $code, $previous);

        if (Config::get('app.debug', false)) {
            dd(get_class($this), [
                ' where   ' => get_class($object),
                ' file    ' => str_replace(base_path(''), '', $this->getFile()),
                ' line    ' => $this->getLine(),
                ' message ' => $message,
                ' code    ' => $code,
                $previous
            ]);
        }
    }
}