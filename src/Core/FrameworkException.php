<?php

/**
 * FrameworkExcepion
 */

namespace System\Core;

use Throwable;

/**
 * FrameworkExcepion raised when there is an excptions occured other than application
 */
class FrameworkException extends \Exception
{
    public const UNKNOWN_ERROR = 100;
    public const INVALID_SESSION_ERROR = 101;
    public const DB_CONNECTION_ERROR = 102;

    /**
     * Instantiate new FrameworkException instance
     *
     * @param string    $message  Message
     * @param int       $code     Code
     * @param Throwable $previous Previous exception
     */
    public function __construct(
        $message = 'Framework Exception',
        $code = 0,
        ?Throwable $previous = null
    ) {
        $code = $code != 0 ? $code : self::UNKNOWN_ERROR;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns exception details
     *
     * @return string
     */
    public function __toString(): string
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
