<?php

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Loader;
use Logger\Log;
use PSpell\Config;
use Router\Response\Response;
use Router\Router;
use System\Core\BaseModule;
use System\Core\Constants;
use System\Core\DataModel;
use System\Core\Session;

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
        $url = ltrim($url_path, "/");
        $parts = explode('/', $url, 2);
        $module = $parts[0] ?? '';

        $module = ucfirst($module);
        
        $moduleClass = "App\\Module\\$module\\Module";

        if (class_exists($moduleClass)) {
            Log::getInstance()->info("Found the Module $moduleClass");
            
           return self::runModule($module, $moduleClass, '/'.$parts[1] ?? '');
        }
        $defaultModule = $config->get('default_module');

        if ($defaultModule) {
            $moduleClass = "App\\Module\\$defaultModule\\Module";

            if (class_exists($moduleClass)) {
                Log::getInstance()->info("Default Module class $moduleClass Found");
                $response =  self::runModule($module, $moduleClass, $url_path ?? '');
                if ($response instanceof Response && $response->getStatusCode() != 404) {
                    return $response;
                }
            }
        }
        
        Log::getInstance()->info("No Module class found, fallback to old flow");
        return self::runApp();
    }

    public static function runModule($module, $moduleClass, $url)
    {
        $url = empty($url) ? '/' : $url;
        $obj = new $moduleClass($module);
        return $obj->run($url);
    }

    public static function runApp()
    {
        $ds = DIRECTORY_SEPARATOR;
        $file = APP_DIR . $ds . "src" . $ds . "app"  . $ds . "config" . $ds . "routes";

        // var_export([file_exists($file), $file]);exit ;

        Loader::loadAll($file);
        Router::setPrefix("App\\Controller");
        new BaseModule('');
        Router::setUpModelClass(DataModel::class);
        return Router::run();
    }

    private static function handleEnv($env)
    {

        switch ($env) {
            case Constants::ENV_DEV:
            case Constants::ENV_DEVELOPMENT:
                error_reporting(E_ALL);
                break;
            case Constants::ENV_TESTING:
            case Constants::ENV_PRODUCTION:
                error_reporting(0);
                break;
            default:
                Log::getInstance()->fatal("Invalid enviroment found");
                header('HTTP/1.1 500 Internal Server Error');
                die("Server Error: {$env}");
        }
    }

    private static function setUp()
    {
        $env = defined('ENV') ? ENV : 'dev';
        $config = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => APP_DIR . "/config/" . ENV . '/config.php'], 'config')->load();
        $env = ConfigLoader::getInstance(ConfigLoader::ENV_LOADER, ['file' => APP_DIR . '/.env'], 'env')->load();
        $db = ConfigLoader::getInstance(ConfigLoader::ARRAY_LOADER, ['file' => APP_DIR . "/config/" . ENV . '/db.php'], 'db')->load();
        include APP_DIR . "/config/constants.php";
        // Loader::intialize($config);
        set_exception_handler('exceptionHandler');
        set_error_handler("errHandler");
        date_default_timezone_set($config->get('timezone', 'UTC'));
        Log::getInstance();
    }
}
