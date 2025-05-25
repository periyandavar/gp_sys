<?php

namespace System\Core;

use Database\Orm\Model;

class DataModel extends Model
{
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
