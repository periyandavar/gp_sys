<?php

namespace System\Core\Http\Response;

use Exception;
use Router\Response\Response as BaseResponse;

class Response extends BaseResponse
{
    /**
     * Redirect to a URL.
     */
    public function redirect(string $url, int $status = 302): void
    {
        $this->setStatusCode($status);
        header("Location: $url");
        exit;
    }

    /**
     * Convert an exception to an HTTP response.
     */
    public function handleException(Exception $e): self
    {
        $status = $e->getCode();
        $message = $e->getMessage() ?: 'Internal Server Error';

        $this->setStatusCode($status);
        $this->setBody($message);

        return $this;
    }
}
