<?php

namespace System\Library\Curl;

use stdClass;
use System\Core\Utility;

class Response
{
    private $body;
    private $statusCode;
    private $headers;
    private $error;
    private $errorCode;

    // Constructor to initialize response
    public function __construct($body, $statusCode, $headers, $error = null, $errorCode = 0)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->error = $error;
        $this->errorCode = $errorCode;
    }

    // Get the response body
    public function getBody()
    {
        return $this->body;
    }

    // Get the HTTP status code of the response
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    // Get the response headers
    public function getHeaders()
    {
        return $this->headers;
    }

    // Get any error message (if there was one)
    public function getError()
    {
        return $this->error;
    }

    // Get the error code (if there was an error)
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    // Check if the request was successful (HTTP status 200-299)
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function toModel($modelClass = '')
    {
        // Decode the JSON response to an array
        $data = json_decode($this->body, true);

        // Check if the model class exists
        $is_associative = Utility::isAssociative($data);
        if ($is_associative) {
            return $this->setUpClassObj($data, $modelClass);
        }

        $model = [];

        foreach ($data as $record) {
            $model[] = $this->setUpClassObj($record, $modelClass);
        }

        // Return the populated model
        return $model;
    }

    private function setUpClassObj($data, $modelClass)
    {
        if (empty($modelClass) || !class_exists($modelClass)) {
            // $data = [
            //     'bookAuthor' => $data['bookAuthor']
            // ];
            // var_export(['out' => $this->convertToClasss(new \stdClass(), $data)]);
            // exit;
            return $this->convertToClasss(new \stdClass(), $data);
        }

        // Create an instance of the model
        $model = new $modelClass();
        // Set the model attributes using the data from the response
        if (method_exists($model, 'setAttributes')) {
            $model->setAttributes($data ?? []);
        } else {
            $model = $this->convertToClasss($model, $data);
        }

        return $model;
    }

    private function convertToClasss($model, $data)
    {
        // var_export(['in' => $data]);//exit;
        if (! Utility::isAssociative($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->convertToClasss(new stdClass(), $value);
                } else {
                    $data[$key] = $value;
                }
            }

            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $model->$key = $this->convertToClasss(new stdClass(), $value);
                continue;
            }
            $model->$key = $value;
        }

        return $model;
    }
}
