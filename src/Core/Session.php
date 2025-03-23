<?php

/**
 * Session
 * php version 7.3.5
 *
 */

namespace System\Core;

use Exception;
use Loader\Config\ConfigLoader;
use Logger\Log;

/**
 * Session class set and manage custom session handlers
 *
 */
class Session
{
    private $_driver;

    private static $_instance;

    /**
     * Instantiate the Session instance
     */
    private function __construct()
    {
        $config = ConfigLoader::getConfig('config')->getAll();
        try {
            session_save_path($config['session_save_path']);
            ini_set('session.gc_maxlifetime', $config['session_expiration']);
            $file = 'src/system/core/session/'
            . $config['session_driver']
            . 'Session.php';
            $class = $config['session_driver'] . 'session';
            if (file_exists($file)) {
                include_once "$file";
                $class = "System\Core\\" . $class;
                $this->_driver = new $class();
            } else {
                throw new FrameworkException('Invalid Driver', FrameworkException::INVALID_SESSION_ERROR);
            }
            if (isset($this->_driver)) {
                session_set_save_handler(
                    [$this->_driver, 'open'],
                    [$this->_driver, 'close'],
                    [$this->_driver, 'read'],
                    [$this->_driver, 'write'],
                    [$this->_driver, 'destroy'],
                    [$this->_driver, 'gc']
                );
                register_shutdown_function('session_write_close');
            }
        } catch (Exception $exception) {
            Log::getInstance()->error(
                $exception->getMessage() . ' in ' . $exception->getFile()
                    . ' at line ' . $exception->getLine()
            );
            Log::getInstance()->debug(
                'Unable to Register the file session driver'
            );
        }
    }

    /**
     * Disabling cloning the object from outside the class
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Returns the instance
     *
     * @return Session
     */
    public static function getInstance(): Session
    {
        self::$_instance = self::$_instance ?? new Session();

        return self::$_instance;
    }
}
