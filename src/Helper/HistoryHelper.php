<?php

/**
 * History Helper
 */

namespace System\Helper;

use Loader\Config\ConfigLoader;
use System\Core\Utility;

/**
 * History Helper
 */
class HistoryHelper
{
    /**
     * Stores last 7 accessed URL in cookies
     *
     * @return void
     */
    public static function traceUser()
    {
        $config = ConfigLoader::getConfig('config')->getAll();
        $history = [];
        $cookieExpiration = $config['cookie_expiration'];
        $cookieName = 'history';
        (isset($_COOKIE[$cookieName])) and
            $history = json_decode($_COOKIE[$cookieName], true);
        array_push(
            $history,
            Utility::currentUrl()
        );
        if (count($history) > 7) {
            array_splice($history, 0, count($history) - 7);
        }
        $history = json_encode($history);
        setcookie($cookieName, $history, time() + ($cookieExpiration), '/');
    }
    /**
     * Retrieve histories in cookies
     *
     * @return mixed
     */
    public static function getHistory()
    {
        $cookieName = 'history';
        $history = (isset($_COOKIE[$cookieName]))
            ? json_decode($_COOKIE[$cookieName], true) : null;

        return $history;
    }
}
