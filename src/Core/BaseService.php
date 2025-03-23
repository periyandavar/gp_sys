<?php

/**
 * BaseService
 * php version 7.3.5
 *
 */

namespace System\Core;

use StdClass;

/**
 * BaseService class, Base class for all services
 */
class BaseService
{
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
}
