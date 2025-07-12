<?php

namespace System;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Router\Response\Response;
use Router\Router;
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

    /**
     * Returns the current application context.
     *
     * @return Context
     */
    private static function getContext()
    {
        if (!isset(self::$context)) {
            self::$context = Utility::getContext();
        }

        return self::$context;
    }

    /**
     * Runs the application based on the current context.
     *
     * @return mixed
     *
     * @throws FrameworkException
     */
    public static function run()
    {
        $context = self::getContext();
        self::init();
        if ($context instanceof ConsoleContext) {
            return self::runConsole();
        } elseif ($context instanceof WebContext) {
            return self::runWebApp();
        } else {
            throw new FrameworkException('Invalid context type. Expected ConsoleContext or WebContext.', FrameworkException::INVALID_CONTEXT);
        }
    }

    /**
     * Loads the service configuration and initializes the services.
     */
    private static function loadServices()
    {
        $services = require_once self::getConfigPath('services');
        Container::loadFromConfig($services);
    }

    /**
     * Returns the path to the configuration file.
     *
     * @param string $name The name of the configuration file (without extension).
     *
     * @return string The full path to the configuration file.
     */
    private static function getConfigPath(string $name)
    {
        return 'config' . DIRECTORY_SEPARATOR . $name . '.php';
    }

    /**
     * Runs the web application.
     *
     * @return Response
     */
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

    /**
     * Returns the class name for a given module.
     *
     * @param string $name The name of the module.
     *
     * @return string The fully qualified class name of the module.
     */
    public static function getModuleClass(string $name)
    {
        return 'App\\Module\\' . ucfirst($name) . '\\Module';
    }

    /**
     * Checks if a module is valid by verifying if its class exists.
     *
     * @param string $name The name of the module.
     *
     * @return bool True if the module class exists, false otherwise.
     */
    public static function isValidModule(string $name)
    {
        return class_exists(self::getModuleClass($name));
    }

    /**
     * Runs a module with the given URL.
     *
     * @param string $module      The name of the module.
     * @param string $moduleClass The fully qualified class name of the module.
     * @param string $url         The URL to run the module with.
     *
     * @return mixed The response from the module's run method.
     */
    public static function runModule(string $module, string $moduleClass, string $url)
    {
        $url = empty($url) ? '/' : $url;
        $obj = new $moduleClass($module);

        return $obj->run($url);
    }

    /**
     * Runs the console application.
     *
     * @return mixed
     *
     * @throws ConsoleException
     * @throws FrameworkException
     */
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

        return $runner->execute();
    }

    /**
     * Initializes the application by loading services and setting up configuration.
     */
    private static function init()
    {
        self::loadServices();
        self::setUpConfig();
        $config = ConfigLoader::getConfig('config');
        set_exception_handler([new self(), 'exceptionHandler']);
        set_error_handler([new self(), 'errHandler']);
        date_default_timezone_set($config->get('timezone', 'UTC'));
    }

    /**
     * Handles the environment settings based on the defined environment.
     *
     * @param string $env The current environment.
     */
    private static function handleEnv(string $env)
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

    /**
     * Loads a configuration file based on the provided parameters.
     *
     * @param string $config_file        The path to the configuration file.
     * @param string $config_type        The type of configuration loader to use.
     * @param string $name               The name of the configuration (default: Constants::CONFIG).
     * @param bool   $can_suppress_error Whether to suppress errors if the file is not found (default: false).
     *
     * @return mixed The loaded configuration.
     *
     * @throws FrameworkException If the configuration file does not exist and errors cannot be suppressed.
     */
    private static function loadConfigFile(string $config_file, string $config_type, string $name = Constants::CONFIG, bool $can_suppress_error = false)
    {
        if (file_exists($config_file)) {
            return ConfigLoader::getInstance($config_type, ['file' => $config_file], $name)->load();
        }

        if ($can_suppress_error) {
            return ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [], $name)->load();
        }

        throw new FrameworkException("Config File $config_file not found to initialize the application configuration", FrameworkException::FILE_NOT_FOUND);
    }

    /**
     * Loads the database configuration based on the current environment.
     *
     * @throws FrameworkException If the database configuration file does not exist and errors cannot be suppressed.
     */
    public static function loadDbConfig()
    {
        $env = defined('ENV') ? ENV : Constants::ENV_DEV;
        $can_suppress_error = self::canSuppressErrors($env);
        $db_config_file = 'config/' . $env . '/db.php';
        self::loadConfigFile($db_config_file, ConfigLoader::ARRAY_LOADER, Constants::DB, $can_suppress_error);

        $app_const_file = self::getConfigPath('constants');
        if (file_exists($app_const_file)) {
            include_once $app_const_file;
        }
    }

    /**
     * Sets up the application configuration by loading the environment file and database configuration.
     */
    private static function setUpConfig()
    {
        $env = defined('ENV') ? ENV : Constants::ENV_DEV;
        self::handleEnv($env);
        $env_file = '.env';
        $can_suppress_error = self::canSuppressErrors($env);
        self::loadConfigFile($env_file, ConfigLoader::ENV_LOADER, Constants::ENV, $can_suppress_error);
        self::loadDbConfig();
        if ($can_suppress_error) {
            Container::get('log')->info('The application is set to suppress the system error. only the serious system and the application error will be thrown.');
        }
    }

    /**
     * Checks if the application can suppress errors based on the environment.
     *
     * @param string $env The current environment.
     *
     * @return bool True if errors can be suppressed, false otherwise.
     */
    public static function canSuppressErrors(string $env = Constants::ENV_DEV)
    {
        if (defined('SUPPRESS_SYSTEM_ERRORS')) {
            return SUPPRESS_SYSTEM_ERRORS;
        }

        if (in_array($env, Constants::TESTING_ENVS)) {
            return false;
        }

        return true;
    }

    public function errHandler($errNo, $errMsg, $errFile, $errLine)
    {
        $context = self::getContext();
        $context->getLogger()->error(
            $errMsg . ' in ' . $errFile . ' at line ' . $errLine
        );

        if ($context instanceof ConsoleContext) {
            echo "An error occurred. Please check the logs for more details.\n";

            return;
        }

        ob_get_contents() and ob_end_clean();
        Router::error();
    }

    public function exceptionHandler($exception)
    {
        $context = self::getContext();
        $context->getLogger()->error(
            $exception->getMessage() . ' in ' . $exception->getFile() . ' at line '
                . $exception->getLine()
        );

        if ($context instanceof ConsoleContext) {
            echo "An exception occurred. Please check the logs for more details.\n";

            return;
        }

        ob_get_contents() and ob_end_clean();
        Router::error();
    }
}
