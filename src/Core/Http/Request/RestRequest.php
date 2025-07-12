<?php

namespace System\Core\Http\Request;

class RestRequest extends Request
{
    /**
     * Get the request content type (e.g., application/json).
     */
    public function getContentType(): ?string
    {
        return $this->server('CONTENT_TYPE');
    }

    /**
     * Get the raw request body.
     */
    public function getRawBody(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * Parse and return JSON body as array or object.
     */
    public function getJsonBody(bool $asArray = true)
    {
        $raw = $this->getRawBody();

        return json_decode($raw, $asArray);
    }

    /**
     * Get the HTTP Authorization header.
     */
    public function getAuthorization(): ?string
    {
        return $this->server('HTTP_AUTHORIZATION') ?? $this->server('REDIRECT_HTTP_AUTHORIZATION');
    }

    /**
     * Get the HTTP method override (for clients using POST with _method).
     */
    public function getMethodOverride(): ?string
    {
        $method = $this->post('_method');

        return $method ? strtoupper($method) : null;
    }
}
