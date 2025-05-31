<?php

use Logger\Log;
use Router\Response\Response;
use Router\Router;
use System\Core\Constants;
use System\Core\Session;

require "vendor/autoload.php";
require "App.php";

define("VALID_REQ", true);
define("APP_DIR", __DIR__);
define("ENV", Constants::ENV_DEV);
define('DS', DIRECTORY_SEPARATOR);

/**
 * Define error handler
 */
if (!function_exists("errHandler")) {
    /**
     * Error handler
     *
     * @param $errNo   Error level
     * @param $errMsg  Error Message
     * @param $errFile Error File
     * @param $errLine Error Line
     *
     * @return void
     */
    function errHandler($errNo, $errMsg, $errFile, $errLine)
    {
        ob_get_contents() and ob_end_clean();
        Log::getInstance()->error(
            $errMsg . ' in ' . $errFile . ' at line ' . $errLine
        );
        Router::error();
    }
}

/**
 * Define exception handler
 */
if (!function_exists("exceptionHandler")) {
    /**
     * Error handler
     *
     * @param $exception Exception object
     *
     * @return void
     */
    function exceptionHandler($exception)
    {
        ob_get_contents() and ob_end_clean();
        Log::getInstance()->error(
            $exception->getMessage() . " in " . $exception->getFile() ." at line "
                . $exception->getLine()
        );
        Router::error();
    }
}

$output = App::run();
 
if ($output instanceof Response) {
    $output->send();
    return;
}
var_export($output);
ob_start();
ob_end_clean();
echo $output;
