<?php

namespace System\Core;

use Database\Database;
use Database\DatabaseFactory;
use Database\DBQuery;
use Loader\Config\ConfigLoader;
use Logger\Log;

/**
 * Super class for all Model. All Model class should extend this Model.
 * BaseModel class consists of basic level functions for various purposes
 *
 */
class BaseModel
{
    /**
     * Database connection variable
     *
     * @var ?Database $db
     */
    protected $db;

    protected $dbQuery;

    /**
     * Instantiate the new BaseModel instance
     */
    public function __construct()
    {
        $this->db = Utility::getDb();
        if (! $this->db) {
            throw new FrameworkException(
                'Database connection is not established. Please check your database configuration.',
                FrameworkException::DB_CONNECTION_ERROR
            );
        }

        $this->dbQuery = new DBQuery();
        Log::getInstance()->info(
            'The ' . static::class . ' class is initalized successfully'
        );
    }
}
