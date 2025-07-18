<?php

namespace System\Library\Curl;

use Logger\Log;

class Request
{
    private $ch;
    private $url;
    private $headers = [];
    private $response = null;
    private $error = null;
    private $errorCode = null;
    private $requestMethod;
    private $data;

    // Constructor to initialize cURL
    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * Set the URL for the request.
     *
     * @param  string $url
     * @return static
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set Headers for the request
     *
     * @param  array  $headers
     * @return static
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the data for the request.
     *
     * @param  mixed  $data
     * @return static
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set the request method (GET, POST, PUT, DELETE, etc.)
     *
     * @param  string $method
     * @return static
     */
    public function setRequestMethod($method)
    {
        $this->requestMethod = strtoupper($method);

        return $this;
    }

    /**
     * Magic method to handle dynamic method calls.
     *
     * @param string $method
     * @param array  $args
     *
     * @return Response
     */
    public function __call($method, $args)
    {
        $this->setData($args);
        $this->setRequestMethod($method);

        return $this->execute();
    }

    /**
     * Execute the cURL request and return the response.
     *
     * @return Response
     */
    public function execute()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);

        // Set the headers if provided
        if (!empty($this->headers)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        }

        // Handle different HTTP request methods
        switch ($this->requestMethod) {
            case 'POST':
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);
                break;
            case 'PUT':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->data);
                break;
            case 'DELETE':
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'GET':
            default:
                curl_setopt($this->ch, CURLOPT_HTTPGET, true);
                break;
        }

        Log::getInstance()->info("Curl Req: [$this->requestMethod]: $this->url : ", $this->data);

        $response = curl_exec($this->ch);

        // Get HTTP status code
        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // Get response headers
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Check for errors
        $error = curl_error($this->ch);
        $errorCode = curl_errno($this->ch);

        // Close the cURL session
        curl_close($this->ch);

        Log::getInstance()->info("Curl Resp: [$this->requestMethod]: $this->url : ", [$body, $statusCode, $headers, $error, $errorCode]);

        // Return a CurlResponse object
        return new Response($body, $statusCode, $headers, $error, $errorCode);
    }

    /**
     * Get the response from the cURL request.
     *
     * @return Response|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get the error message from the cURL request.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get the error code from the cURL request.
     *
     * @return int|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
