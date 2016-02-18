<?php namespace Crip\Core\Exceptions;

use Config;
use Crip\Core\Contracts\ICripObject;
use Exception;

/**
 * Class BaseCripException
 * @package Crip\Core\Exceptions
 */
class BaseCripException extends Exception
{

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param ICripObject $object
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     * @since 5.1.0
     */
    public function __construct(ICripObject $object, $message, $code = 0, Exception $previous = null)
    {
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