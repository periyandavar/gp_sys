<?php

namespace System\Core;

use Database\Database;
use Database\DatabaseFactory;
use Loader\Config\ConfigLoader;

/**
 * DatabaseSession class, custom session handler
 */
class DatabaseSession implements \SessionHandlerInterface
{
    /**
     * Database connection object
     *
     * @var ?Database
     */
    private $db;

    /**
     * Session Table Name
     *
     * @var string
     */
    private $_table;

    private $security;

    /**
     * Establish Db connection
     *
     * @return void
     */
    public function connect()
    {
        $config = ConfigLoader::getConfig('db')->getAll();
        DatabaseFactory::setUpConfig($config);
        $this->db = DatabaseFactory::get();
    }

    /**
     * Session open
     *
     * @param $savePath    Session path
     * @param $sessionName Session name
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        $key = 'bRuD5WYw5wd0rdHR9yLlM6wt2vteuiniQBqE70nAuhU=';
        $iv = '1234567891011121';
        $method = 'aes-128-cbc';
        $this->security = new Security($method, $key, 0, $iv);
        $this->connect();
        $this->_table = $savePath;

        return true;
    }

    /**
     * Session close
     *
     * @return bool
     */
    public function close(): bool
    {
        $this->db = null;

        return true;
    }

    /**
     * Reads data from session
     *
     * @param $sessionId Session Id
     *
     * @return string
     */
    public function read($sessionId): string
    {
        $this->db->select('data')
            ->from($this->_table)
            ->where('sessionId', '=', $sessionId);
        $this->db->execute();
        if ($row = $this->db->fetch()) {
            if (!is_object($row)) {
                return '';
            }

            return ($data = $this->security->decrypt($row->data)) ? (string) $data : '';
        } else {
            return '';
        }
    }

    /**
     * Writes data to the session db
     *
     * @param $sessionId Session id
     * @param $data      Session data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $access = time();
        $data = $this->security->encrypt($data);
        $this->db->select('id')
            ->from($this->_table)
            ->where('sessionId', '=', $sessionId)
            ->limit(1);
        $this->db->execute();
        if ($this->db->fetch()) {
            $this->db->update($this->_table, ['access' => $access, 'data' => $data])
                ->where('sessionId', '=', $sessionId)->limit(1);

            return $this->db->execute();
        } else {
            $this->db->insert(
                $this->_table,
                ['sessionId' => $sessionId, 'access' => $access, 'data' => $data]
            );

            return $this->db->execute();
        }
    }

    /**
     * Destroy sessions
     *
     * @param $sessionId Session Id
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->db->delete($this->_table, ['sessionId', '=', $sessionId]);

        return $this->db->execute();
    }

    /**
     * Session grabage collector
     *
     * @param int $max Maximum lifetime
     *
     * @return int
     */
    public function gc($max): int
    {
        $old = time() - $max;
        $this->db->delete($this->_table, ['access', '<', $old]);

        return (int) $this->db->execute();
    }
}
