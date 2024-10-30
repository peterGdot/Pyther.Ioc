<?php
namespace Pyther\Ioc\Exceptions;

/**
 * An exception that occurs during the resolve process.
 */
class ResolveException extends \Exception
{
    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?? "", $code, $previous);
    }
} 