<?php

/**
 * Session
 * php version 7.3.5
 *
 */

namespace System\Core\Session;

use Exception;
use Loader\Config\ConfigLoader;
use Logger\Log;
use System\Core\Constants;

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
        $config = ConfigLoader::getConfig(Constants::CONFIG)->getAll();
        try {
            $session_path = $config['session_save_path'] ?? '';
            if (is_dir($session_path)) {
                session_save_path($session_path);
            }
            ini_set('session.gc_maxlifetime', $config['session_expiration'] ?? 36000);
            $_driver = $config['session_driver'] ?? '';
            if (empty($_driver)) {
                return;
            }
            $_driver .= 'session';
            if (class_exists($_driver)) {
                $this->_driver = new $_driver();
            } else {
                Log::getInstance()->warning('Invalid Session Driver: ' . $_driver);
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
