<?php

namespace System\Core\Http\Response;

class RestResponse extends Response
{
    /**
     * RestResponse constructor.
     *
     * @param int    $status  HTTP status code.
     * @param array  $headers HTTP headers.
     * @param string $body    Response body.
     */
    public function __construct($status = 200, $headers = [], $body = '')
    {
        parent::__construct($status, $headers, $body, static::TYPE_JSON);
    }

    /**
     * Send a JSON response.
     */
    public function json($data, int $status = 200): void
    {
        $this->setStatusCode($status);
        $this->setBody(json_encode($data));
        $this->send();
    }

    /**
     * Send a response for a created resource.
     */
    public function created($data = null, string $location = ''): void
    {
        $this->setStatusCode(201);
        if ($location) {
            $this->setHeader('Location', $location);
        }
        $body = $data !== null ? json_encode($data) : '';
        $this->setBody($body);
        $this->send();
    }

    /**
     * Send a response with no content.
     */
    public function noContent(): void
    {
        $this->setStatusCode(204);
        $this->setBody('');
        $this->send();
    }

    /**
     * Send a response for a successful update.
     */
    public function updated($data = null): void
    {
        if ($data !== null) {
            $this->json($data, 200);
        } else {
            $this->noContent();
        }
    }

    /**
     * Send an error response in JSON format.
     */
    public function error(string $message, int $status = 400, $extra = []): void
    {
        $body = array_merge([
            'error' => true,
            'message' => $message,
            'status' => $status,
        ], $extra);

        $this->json($body, $status);
    }

    /**
     * Handle exceptions and send an error response.
     *
     * @param  \Exception $e
     * @return Response
     */
    public function handleException(\Exception $e): Response
    {
        $this->setStatusCode(500);
        $this->setBody(json_encode([
            'error' => true,
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ]));

        return $this;
    }
}
