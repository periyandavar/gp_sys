<?php

/**
 * Constants class constants are defined here
 */

namespace System\Core;

/**
 * Constants Class used to access the Constants
 *
 */
final class Constants
{
    public const METHOD_GET = 'get';

    public const METHOD_POST = 'post';

    public const METHOD_PUT = 'put';

    public const METHOD_DELETE = 'delete';

    public const ENV_DEV = 'dev';
    public const ENV_PROD = 'prod';

    public const ENV_TEST = 'test';
    public const ENV_LOCAL = 'local';

    public const METHOD_PATCH = 'patch';

    public const CONFIG_OVER_WRITE = [
        self::ENV_LOCAL,
        self::ENV_DEV,
        self::ENV_TEST,
        self::ENV_PROD
    ];

    public const TESTING_ENVS = [
        self::ENV_DEV,
        self::ENV_TEST,
        self::ENV_LOCAL
    ];

    public const VALID_ENV = [
        self::ENV_DEV,
        self::ENV_PROD,
        self::ENV_TEST,
        self::ENV_LOCAL
    ];

    public const CONFIG = 'config';
    public const ENV = 'env';
    public const DB = 'db';

    public static function isValidEnv(string $env): bool
    {
        return in_array($env, self::VALID_ENV, true);
    }
}
