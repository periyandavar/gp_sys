<?php

namespace System;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Router\Response\Response;
use System\Core\Base\Context\ConsoleContext;
use System\Core\Base\Context\Context;
use System\Core\Base\Context\WebContext;
use System\Core\Console;
use System\Core\Constants;
use System\Core\Exception\ConsoleException;
use System\Core\Exception\FrameworkException;
use System\Core\Session\Session;
use System\Core\Utility;

class App
{
    private static Context $context;

    private static function getContext()
    {
        if (!isset(self::$context)) {
            self::$context = Utility::getContext();
        }

        return self::$context;
    }
    public static function run()
    {
        $context = self::getContext();
        self::loadServices();
        self::init();
        if ($context instanceof ConsoleContext) {
            return self::runConsole();
        } elseif ($context instanceof WebContext) {
            return self::runWebApp();
        } else {
            throw new FrameworkException('Invalid context type. Expected ConsoleContext or WebContext.', FrameworkException::INVALID_CONTEXT);
        }
    }

    private static function loadServices()
    {
        $services = require_once 'config/services.php';
        Container::loadFromConfig($services);
    }

    private static function runWebApp()
    {
        $handle_session = ConfigLoader::getConfig('config')->get('handle_session', true);
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
            Container::get('log')->info("Found the Module $moduleClass");

            return self::runModule($module, $moduleClass, '/' . Utility::coalesceArray($parts, 1, ''));
        }

        Container::get('log')->info("Module class $moduleClass not found, checking for default module");
        $defaultModule = ucfirst($config->get('default_module', ''));

        if ($defaultModule) {
            $moduleClass = "App\\Module\\$defaultModule\\Module";

            if (class_exists($moduleClass)) {
                Container::get('log')->info("Default Module class $moduleClass Found");
                $response = self::runModule($module, $moduleClass, $url_path);

                return $response;
            }
        }

        $response = new Response(404);

        return $response;
    }

    public static function getModuleClass($name)
    {
        return 'App\\Module\\' . ucfirst($name) . '\\Module';
    }

    public static function isValidModule(string $name)
    {
        return class_exists(self::getModuleClass($name));
    }

    public static function runModule($module, $moduleClass, $url)
    {
        $url = empty($url) ? '/' : $url;
        $obj = new $moduleClass($module);

        return $obj->run($url);
    }

    public static function runConsole()
    {
        $context = self::getContext();
        if (! $context instanceof ConsoleContext) {
            throw new FrameworkException('Invalid context type for console execution. Expected ConsoleContext.', FrameworkException::INVALID_CONTEXT);
        }
        $command = $context->getAction();
        $argv = $context->getArgv();
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

        $context->set('command_class', $commandAction);
        $runner = Container::resolveClassConstructor($commandAction, $argv);
        $runner->execute();
    }

    private static function init()
    {
        self::setUpConfig();
        $config = ConfigLoader::getConfig('config');
        // set_exception_handler('exceptionHandler');
        // set_error_handler('errHandler');
        date_default_timezone_set($config->get('timezone', 'UTC'));
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
                Container::get('log')->critical('Invalid enviroment found');
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

    public static function loadDbConfig()
    {
        $env = defined('ENV') ? ENV : 'dev';
        $can_suppress_error = self::canSuppressErrors($env);
        $db_config_file = 'config/' . $env . '/db.php';
        self::loadConfigFile($db_config_file, ConfigLoader::ARRAY_LOADER, Constants::DB, $can_suppress_error);

        $app_const_file = 'config/constants.php';
        if (file_exists($app_const_file)) {
            include_once $app_const_file;
        }
    }

    private static function setUpConfig()
    {
        $env = defined('ENV') ? ENV : 'dev';
        self::handleEnv($env);
        $env_file = '.env';
        $can_suppress_error = self::canSuppressErrors($env);
        self::loadConfigFile($env_file, ConfigLoader::ENV_LOADER, Constants::ENV, $can_suppress_error);
        self::loadDbConfig();
        if ($can_suppress_error) {
            Container::get('log')->info('The application is set to suppress the system error. only the serious system and the application error will be thrown.');
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
