<?php

namespace System\Core\Data;

use Database\Orm\Record;
use Router\Request\Model\Model;
use System\Core\Utility;

class DataRecord extends Record implements Model
{
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function getValues(): array
    {
        $data = parent::getValues();

        return $data;
    }

    public static function getDB()
    {
        return Utility::getDb();
    }

    public function skipInsertOn()
    {
        return [
            'deletionToken'
        ];
    }

    public function useDelete()
    {
        return [
            'deletionToken' => time()
        ];
    }

    public static function getTableName()
    {
        return strtolower(basename(str_replace('\\', '/', static::class)));
    }
}
