<?php

namespace System\Core\Base\Model;

use Database\Database;
use Database\DBQuery;
use Loader\Container;
use System\Core\Base\Context\Context;
use System\Core\Exception\FrameworkException;
use System\Core\Utility;

/**
 * Super class for all Model. All Model class should extend this Model.
 * Model class consists of basic level functions for various purposes
 *
 */
class Model
{
    /**
     * Database connection variable
     *
     * @var ?Database $db
     */
    protected $db;

    protected DBQuery $dbQuery;
    protected Context $context;

    /**
     * Instantiate the new Model instance
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
        $this->context = Container::get('context');
        $this->context->getLogger()->info('The ' . static::class . ' class is initalized successfully');
    }

    /**
     * Find a record by primary key
     */
    public function find($table, $id, $primaryKey = 'id')
    {
        return $this->db->selectAll(false)->from($table)->where([$primaryKey => $id])->getOne();
    }

    /**
     * Get all records from a table
     */
    public function all($table)
    {
        return $this->db->selectAll(false)->from($table)->getAll();
    }

    /**
     * Insert a new record
     */
    public function insert($table, array $data)
    {
        return $this->db->insert($table, $data);
    }

    /**
     * Update a record by primary key
     */
    public function update($table, $id, array $data, $primaryKey = 'id')
    {
        return $this->db->update($table, $data, [$primaryKey => $id]);
    }

    /**
     * Delete a record by primary key
     */
    public function delete($table, $id, $primaryKey = 'id')
    {
        return $this->db->delete($table, [$primaryKey => $id]);
    }
}
