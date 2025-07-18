<?php

namespace System\Core\Base\Service;

use Loader\Container;
use StdClass;
use System\Core\Base\Context\Context;

/**
 * BaseService class, Base class for all services
 */
class Service
{
    protected Context $context;

    public function __construct()
    {
        $this->context = Container::get('context');
    }

    /**
     * Converts the array into object
     *
     * @param array $data data
     *
     * @return object
     */
    public function toObject(array $data): object
    {
        $obj = new StdClass();
        foreach ($data as $key => $value) {
            $obj->$key = $value;
        }

        return $obj;
    }

    /**
     * Converts the array into array of object
     *
     * @param array $data data
     *
     * @return array
     */
    public function toArrayObjects(array $data): array
    {
        $result = [];
        foreach ($data as $record) {
            $obj = new stdClass();
            foreach ($record as $key => $value) {
                $obj->$key = $value;
            }
            $result[] = $obj;
        }

        return $result;
    }

    /**
     * Get a record by ID using the model
     *
     * @param  object      $model
     * @param  mixed       $id
     * @return object|null
     */
    public function getById($model, $id)
    {
        return $model->find($id);
    }

    /**
     * Get all records using the model
     *
     * @param  object $model
     * @return array
     */
    public function getAll($model): array
    {
        return $model->all();
    }

    /**
     * Create a new record using the model
     *
     * @param  object $model
     * @param  array  $data
     * @return mixed
     */
    public function create($model, array $data)
    {
        return $model->insert($data);
    }

    /**
     * Update a record by ID using the model
     *
     * @param  object $model
     * @param  mixed  $id
     * @param  array  $data
     * @return mixed
     */
    public function update($model, $id, array $data)
    {
        return $model->update($id, $data);
    }

    /**
     * Delete a record by ID using the model
     *
     * @param  object $model
     * @param  mixed  $id
     * @return mixed
     */
    public function delete($model, $id)
    {
        return $model->delete($id);
    }
}
