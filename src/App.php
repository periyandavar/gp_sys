<?php

namespace System;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Logger\Log;
use Router\Response\Response;
use System\Core\Console;
use System\Core\Constants;
use System\Core\Exception\ConsoleException;
use System\Core\Exception\FrameworkException;
use System\Core\Session\Session;
use System\Core\Utility;

class App
{
    public static function run($handle_session = true)
    {
        self::setUp();
        if ($handle_session) {
            Session::getInstance(); // Initialize the Session
            session_start();
        }

        $url = $_SERVER['REQUEST_URI'];
        $config = ConfigLoader::getConfig('config');
        $url = str_replace('/index.php', '', $url);
        $url_path = str_replace($config->get('base_url'), '', $url);
        $url = ltrim($url_path, '/');
        $parts = explode('/', $url, 2);
        $module = Utility::coalesceArray($parts, 0, '');

        $module = ucfirst($module);

        $moduleClass = "App\\Module\\$module\\Module";

        if (class_exists($moduleClass)) {
            Log::getInstance()->info("Found the Module $moduleClass");

            return self::runModule($module, $moduleClass, '/' . Utility::coalesceArray($parts, 1, ''));
        }

        Log::getInstance()->info("Module class $moduleClass not found, checking for default module");
        $defaultModule = ucfirst($config->get('default_module', ''));

        if ($defaultModule) {
            $moduleClass = "App\\Module\\$defaultModule\\Module";

            if (class_exists($moduleClass)) {
                Log::getInstance()->info("Default Module class $moduleClass Found");
                $response = self::runModule($module, $moduleClass, $url_path);

                return $response;
            }
        }

        $response = new Response(404);

        return $response;
    }

    public static function runModule($module, $moduleClass, $url)
    {
        $url = empty($url) ? '/' : $url;
        $obj = new $moduleClass($module);

        return $obj->run($url);
    }
    private static function loadConfig($is_console = false)
    {
        $envs = Constants::CONFIG_OVER_WRITE;

        foreach ($envs as $env) {
            $ds = DIRECTORY_SEPARATOR;
            $dir = __DIR__ . $ds . 'config' . $ds . $env . $ds;

            $files = glob($dir . '*.php');
            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                ConfigLoader::loadConfig($file, $name, 'a');
            }
            $environment = defined('ENV') ? ENV : Constants::ENV_LOCAL;
            if ($environment === $env) {
                break;
            }
        }

        $config = $is_console ? __DIR__ . $ds . 'console' . $ds . 'config.php' : __DIR__ . $ds . 'src' . $ds . 'config.php';
        if (file_exists($config)) {
            ConfigLoader::loadConfig($config, 'config', 'a');
        }
    }

    public static function runConsole($action, $argv = [])
    {
        self::loadConfig(true);
        self::initRun();
        $command = $action;
        $commandAction = Console::getCommandAction($command);
        if ($commandAction === null) {
            $appDir = defined('APP_DIR') ? APP_DIR : '';
            $commandFile = $appDir . '/commands.php';
            if (file_exists($commandFile)) {
                $commands = require_once $commandFile;
                $commandAction = $commands[$command] ?? null;
            }
        }
        if (empty($commandAction) || !class_exists($commandAction)) {
            throw new ConsoleException("Command $command not found. Please check the command name.");
        }

        $runner = Container::resolveClassConstructor($commandAction, $argv);
        $runner->execute();
    }

    private static function initRun()
    {
        $config = ConfigLoader::getConfig('config');
        set_exception_handler('exceptionHandler');
        set_error_handler('errHandler');
        date_default_timezone_set($config->get('timezone', 'UTC'));
        Log::getInstance();

        $env = defined('ENV') ? ENV : 'dev';

        $can_suppress_error = self::canSuppressErrors($env);

        if ($can_suppress_error) {
            Log::getInstance()->info('The application is set to suppress the system error. only the serious system and the application error will be thrown.');
        }
    }

    private static function handleEnv($env)
    {
        switch ($env) {
            case Constants::ENV_DEV:
            case Constants::ENV_LOCAL:
                error_reporting(E_ALL);
                break;
            case Constants::ENV_TEST:
            case Constants::ENV_PROD:
                error_reporting(0);
                break;
            default:
                Log::getInstance()->fatal('Invalid enviroment found');
                header('HTTP/1.1 500 Internal Server Error');
                die("Server Error: {$env}");
        }
    }

    private static function loadConfigFile($config_file, $config_type, $name = Constants::CONFIG, $can_suppress_error = false)
    {
        if (file_exists($config_file)) {
            return ConfigLoader::getInstance($config_type, ['file' => $config_file], $name)->load();
        }

        if ($can_suppress_error) {
            return ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], $name)->load();
        }

        throw new FrameworkException("Config File $config_file not found to initialize the application configuration", FrameworkException::FILE_NOT_FOUND);
    }

    private static function setUp()
    {
        $env = defined('ENV') ? ENV : 'dev';

        $can_suppress_error = self::canSuppressErrors($env);
        self::handleEnv($env);

        $env_file = '.env';
        self::loadConfigFile($env_file, ConfigLoader::ENV_LOADER, Constants::ENV, $can_suppress_error);
        $appDir = defined('APP_DIR') ? APP_DIR : '';
        $config_file = $appDir . '/config/' . $env . '/config.php';
        $config = self::loadConfigFile($config_file, ConfigLoader::ARRAY_LOADER, Constants::CONFIG, $can_suppress_error);

        $db_config_file = $appDir . '/config/' . $env . '/db.php';
        self::loadConfigFile($db_config_file, ConfigLoader::ARRAY_LOADER, Constants::DB, $can_suppress_error);

        $app_const_file = $appDir . '/config/constants.php';
        if (file_exists($app_const_file)) {
            include_once $app_const_file;
        }

        set_exception_handler('exceptionHandler');
        set_error_handler('errHandler');
        date_default_timezone_set($config->get('timezone', 'UTC'));
        Log::getInstance();

        if ($can_suppress_error) {
            Log::getInstance()->info('The application is set to suppress the system error. only the serious system and the application error will be thrown.');
        }
    }

    public static function canSuppressErrors($env = Constants::ENV_DEV)
    {
        if (defined('SUPPRESS_SYSTEM_ERRORS')) {
            return SUPPRESS_SYSTEM_ERRORS;
        }

        if (in_array($env, Constants::TESTING_ENVS)) {
            return false;
        }

        return true;
    }
}
