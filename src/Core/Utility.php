<?php

/**
 * Utility
 */

namespace System\Core;

use Database\Database;
use Database\DatabaseFactory;
use Loader\Config\ConfigLoader;
use SimpleXMLElement;
use Symfony\Component\Yaml\Yaml;

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
     * @param  string        $name
     * @return Database|null
     */
    public static function getDb(string $name = 'default')
    {
        static $db = DatabaseFactory::get($name);
        if (!$db) {
            $dbConfig = ConfigLoader::getConfig('db')->getAll();
            DatabaseFactory::setUpConfig($dbConfig);
            $db = DatabaseFactory::get($name);
        }

        return $db;
    }

    public static function getDirContents(string $dir)
    {
        $files = [];
        if (!(is_dir($dir))) {
            return $files;
        }
        foreach (glob("$dir/*.php") as $filename) {
            $files[] = $filename;
        }

        return $files;
    }

    public static function arrayToXml($data, $rootElement = '<root/>')
    {
        $xml = new SimpleXMLElement($rootElement);
        self::arrayToXmlRecursive($data, $xml);

        return $xml->asXML();
    }

    public static function arrayToXmlRecursive($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                self::arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    public static function arrayToCsv($data)
    {
        if (empty($data) || !is_array($data)) {
            return '';
        }

        ob_start();
        $output = fopen('php://output', 'w');

        // If the first element is an associative array, use the keys as headers
        if (isset($data[0]) && is_array($data[0])) {
            fputcsv($output, array_keys($data[0])); // Add headers
        }

        foreach ($data as $row) {
            fputcsv($output, (array) $row);
        }

        fclose($output);

        return ob_get_clean();
    }

    public static function arrayToYaml($data)
    {
        return Yaml::dump($data);
    }

    public static function isAssociative($array)
    {
        if (empty($array)) {
            return false;
        }

        // Get the keys of the array
        $keys = array_keys($array);

        // Check if the keys are sequential (starting from 0, incrementing by 1)
        return $keys !== range(0, count($array) - 1);
    }
}
