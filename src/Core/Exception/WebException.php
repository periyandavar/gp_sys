<?php

namespace System\Core\Exception;

class WebException extends FrameworkException
{
    // Common HTTP status codes
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const CONFLICT = 409;
    public const UNPROCESSABLE_ENTITY = 422;
    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;

    protected int $statusCode;

    /**
     * WebException constructor.
     *
     * @param string          $message    The error message.
     * @param int             $statusCode The HTTP status code.
     * @param \Throwable|null $previous   The previous exception, if any.
     */
    public function __construct(
        string $message = '',
        int $statusCode = self::INTERNAL_SERVER_ERROR,
        ?\Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    // Static helpers for common HTTP exceptions
    public static function badRequest(string $message = 'Bad Request'): self
    {
        return new self($message, self::BAD_REQUEST);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, self::UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, self::FORBIDDEN);
    }

    public static function notFound(string $message = 'Not Found'): self
    {
        return new self($message, self::NOT_FOUND);
    }

    public static function methodNotAllowed(string $message = 'Method Not Allowed'): self
    {
        return new self($message, self::METHOD_NOT_ALLOWED);
    }

    public static function conflict(string $message = 'Conflict'): self
    {
        return new self($message, self::CONFLICT);
    }

    public static function unprocessableEntity(string $message = 'Unprocessable Entity'): self
    {
        return new self($message, self::UNPROCESSABLE_ENTITY);
    }

    public static function internalServerError(string $message = 'Internal Server Error'): self
    {
        return new self($message, self::INTERNAL_SERVER_ERROR);
    }

    public static function notImplemented(string $message = 'Not Implemented'): self
    {
        return new self($message, self::NOT_IMPLEMENTED);
    }

    public static function badGateway(string $message = 'Bad Gateway'): self
    {
        return new self($message, self::BAD_GATEWAY);
    }

    public static function serviceUnavailable(string $message = 'Service Unavailable'): self
    {
        return new self($message, self::SERVICE_UNAVAILABLE);
    }
}
