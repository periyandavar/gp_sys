<?php

namespace System\Core\Exception;

class ConsoleException extends FrameworkException
{
    public const INVALID_ARGUMENT = 1;
    public const COMMAND_NOT_FOUND = 2;
    public const EXECUTION_FAILED = 3;
    public const PERMISSION_DENIED = 4;
    public const UNKNOWN_ERROR = 255;

    protected int $errorCode;

    public function __construct(
        string $message = '',
        int $errorCode = self::UNKNOWN_ERROR,
        \Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        parent::__construct($message, $errorCode, $previous);
    }

    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    // Static helpers for common console exceptions
    public static function invalidArgument(string $message = 'Invalid argument'): self
    {
        return new self($message, self::INVALID_ARGUMENT);
    }

    public static function commandNotFound(string $message = 'Command not found'): self
    {
        return new self($message, self::COMMAND_NOT_FOUND);
    }

    public static function executionFailed(string $message = 'Execution failed'): self
    {
        return new self($message, self::EXECUTION_FAILED);
    }

    public static function permissionDenied(string $message = 'Permission denied'): self
    {
        return new self($message, self::PERMISSION_DENIED);
    }

    public static function unknownError(string $message = 'Unknown error'): self
    {
        return new self($message, self::UNKNOWN_ERROR);
    }
}
