<?php

namespace System\Core\Data;

use Database\Database;
use Database\Orm\Record;
use Router\Request\Model\Model;
use System\Core\Utility;

class DataRecord extends Record implements Model
{
    /**
     * Set the values
     *
     * @param array $values
     *
     * @return void
     */
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Get the values
     *
     * @return array
     */
    public function getValues(): array
    {
        $data = parent::getValues();

        return $data;
    }

    /**
     * Get the database connection.
     *
     * @return Database|null
     */
    public static function getDB()
    {
        return Utility::getDb();
    }

    /**
     * Get the data keys to skip on insert.
     *
     * @return array
     */
    public function skipInsertOn()
    {
        return [
            'deletionToken'
        ];
    }

    /**
     * Get the fields to be used as soft delete.
     *
     * @return array
     */
    public function useDelete()
    {
        return [
            'deletionToken' => time()
        ];
    }

    /**
     * Get the table name for the model.
     *
     * @return string
     */
    public static function getTableName()
    {
        return strtolower(basename(str_replace('\\', '/', static::class)));
    }
}
