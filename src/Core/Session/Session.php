<?php

namespace System\Core\Session;

use Exception;
use Loader\Config\ConfigLoader;
use Loader\Container;
use System\Core\Exception\FrameworkException;
use System\Core\Session\Driver\DatabaseSession;
use System\Core\Session\Driver\FileSession;

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
            $dir = $config['session_save_path'];
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            session_save_path($config['session_save_path']);

            ini_set('session.gc_maxlifetime', $config['session_expiration']);
            // var_export($configp)
            switch ($config['session_driver']) {
                case 'file':
                    $this->_driver = new FileSession();
                    break;
                case 'database':
                    $this->_driver = new DatabaseSession();
                    break;
                default:
                    throw new FrameworkException('Invalid Driver', FrameworkException::INVALID_SESSION_ERROR);
            }
            session_set_save_handler(
                [$this->_driver, 'open'],
                [$this->_driver, 'close'],
                [$this->_driver, 'read'],
                [$this->_driver, 'write'],
                [$this->_driver, 'destroy'],
                [$this->_driver, 'gc']
            );
            register_shutdown_function('session_write_close');
        } catch (Exception $exception) {
            Container::get('log')->error(
                $exception->getMessage() . ' in ' . $exception->getFile()
                    . ' at line ' . $exception->getLine()
            );
            Container::get('log')->debug(
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
