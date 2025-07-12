<?php

namespace System\Library\Curl;

class Curl
{
    private string $base_url;

    private string $end_point = '';

    private array $headers = [];

    private array $data = [];

    /**
     * Constructor for Curl.
     *
     * @param string $base_url
     */
    public function __construct(string $base_url)
    {
        $this->base_url = $base_url;
    }

    /**
     * Call the request
     *
     * @param string $method
     *
     * @return Response
     */
    public function call(string $method = 'GET')
    {
        $curl = new Request();

        return $curl->setUrl($this->getUrl())
            ->setData($this->getData())
            ->setRequestMethod($method)
            ->setHeaders($this->getHeaders())
            ->execute();
    }

    /**
     * Get call
     *
     * @return Response
     */
    public function get()
    {
        return $this->call();
    }

    /**
     * post call
     *
     * @return Response
     */
    public function post()
    {
        return $this->call('POST');
    }

    /**
     * put call
     *
     * @return Response
     */
    public function put()
    {
        return $this->call('PUT');
    }

    /**
     * Delete call
     *
     * @return Response
     */
    public function delete()
    {
        return $this->call('DELETE');
    }

    public function getUrl()
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($this->end_point, '/');
    }

    /**
     * Get the value of end_point
     *
     * @return string
     */
    public function getEndPoint(): string
    {
        return $this->end_point;
    }

    /**
     * Set the value of end_point
     *
     * @param string $end_point
     *
     * @return self
     */
    public function setEndPoint(string $end_point): self
    {
        $this->end_point = $end_point;

        return $this;
    }

    /**
     * Get the value of headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the value of headers
     *
     * @param array $headers
     *
     * @return self
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get the value of data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the value of data
     *
     * @param array $data
     *
     * @return self
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
