<?php

/**
 * FileSession Handler
 */

namespace System\Core\Session\Driver;

use System\Library\Security\Security;

/**
 * Custom Session handler
 *
 */
class FileSession implements \SessionHandlerInterface
{
    private $_savePath;

    private $security;

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
        $this->_savePath = $savePath;
        !is_dir($this->_savePath) and mkdir($this->_savePath, 0777);

        return true;
    }

    /**
     * Session close
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Reads data from session
     *
     * @param string $sessId Session Id
     *
     * @return string
     */
    public function read($sessId): string
    {
        $data = (string) @file_get_contents("$this->_savePath/$sessId");
        $data = $this->security->decrypt($data);
        $data = (string) $data;

        return $data;
    }

    /**
     * Writes data to the session db
     *
     * @param string $sessId Session id
     * @param string $data   Session data
     *
     * @return bool
     */
    public function write($sessId, $data): bool
    {
        $data = $this->security->encrypt($data);

        return file_put_contents("$this->_savePath/$sessId", $data) === false
            ? false
            : true;
    }

    /**
     * Destroy sessions
     *
     * @param string $sessId Session Id
     *
     * @return bool
     */
    public function destroy($sessId): bool
    {
        $file = "$this->_savePath/$sessId";
        file_exists($file) and unlink($file);

        return true;
    }

    /**
     * Session grabage collector
     *
     * @param int $maxlifetime Maximum lifetime
     *
     * @return int
     */
    public function gc($maxlifetime): int
    {
        foreach (glob("$this->_savePath/sess_*") as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return 1;
    }
}
