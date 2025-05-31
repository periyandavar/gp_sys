<?php

/**
 * Utility
 */

namespace System\Core;

use Database\Database;
use Database\DatabaseFactory;
use Loader\Config\ConfigLoader;

/**
 * Utility Class offers various static functions
 *
 */
final class Utility
{
    /**
     * Returns the baseURL
     *
     * @return string
     */
    public static function baseURL(): string
    {
        $config = ConfigLoader::getConfig('config');

        return $config->get('base_url');
    }

    /**
     * Set & unset the session data
     *
     * @param string      $key   Key Name
     * @param string|null $value Value
     *
     * @return void
     */
    public static function setSessionData(string $key, ?string $value)
    {
        if ($value == null) {
            unset($_SESSION[$key]);
        } else {
            $_SESSION[$key] = $value;
        }
    }

    /**
     * Redirects to the passed URL
     *
     * @param string $url       URL
     * @param bool   $permanent Whether the URL is permanent or temporary
     *
     * @return void
     */
    public static function redirectURL(string $url, $permanent = true)
    {
        !headers_sent() and
            header('Location: /' . $url, true, ($permanent === true) ? 301 : 302);
        exit();
    }

    /**
     * Checks whether the string is ends with the given substring
     *
     * @param string $str    String
     * @param string $endStr Substring
     *
     * @return bool
     */
    public static function endsWith(string $str, string $endStr): bool
    {
        $len = strlen($endStr);
        if ($len == 0) {
            return true;
        }

        return (substr($str, -$len) === $endStr);
    }

    /**
     * Checks whether the string is starts with the given substring
     *
     * @param string $str      String
     * @param string $startStr Substring
     *
     * @return bool
     */
    public static function startsWith(string $str, string $startStr): bool
    {
        $len = strlen($startStr);

        return (substr($str, 0, $len) === $startStr);
    }

    /**
     * Returns current URL
     *
     * @return string
     */
    public static function currentUrl(): string
    {
        return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
                ? 'https'
                : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    }

    /**
     * Get DB
     *
     * @param  string   $name
     * @return Database
     */
    public static function getDb(string $name = 'default')
    {
        $db = DatabaseFactory::get($name);
        if (!$db) {
            $dbConfig = ConfigLoader::getConfig('db')->getAll();
            $db = DatabaseFactory::create($dbConfig);
        }

        return $db;
    }
}
