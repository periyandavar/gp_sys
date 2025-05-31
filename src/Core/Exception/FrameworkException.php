<?php

/**
 * FrameworkExcepion
 */

namespace System\Core\Exception;

use Throwable;

/**
 * FrameworkExcepion raised when there is an excptions occured other than application
 */
class FrameworkException extends \Exception
{

    public const UNKNOWN_ERROR = 1000;
    public const INVALID_ARGUMENT = 1001;

    public const INVALID_SESSION_ERROR = 2000;
    
    public const DB_CONNECTION_ERROR = 2100;

    public const FILE_NOT_FOUND = 2200;

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
