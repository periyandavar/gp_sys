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
    public const ENV_PRODUCTION = 'production';

    public const ENV_TESTING = 'testing';

    public const ENV_DEVELOPMENT = 'development';

    public const METHOD_PATCH = 'patch';

    public const TESTING_ENVS = [
        self::ENV_DEV,
        self::ENV_TESTING,
        self::ENV_DEVELOPMENT
    ];

    public const CONFIG = 'config';
    public const ENV = 'env';
    public const DB = 'db';
}
