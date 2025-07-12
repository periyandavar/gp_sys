<?php

namespace System\Core\Base\Context;

use Loader\Config\ConfigLoader;
use Loader\Container;
use System\Core\Constants;

class Context
{
    protected $env;
    /**
     * Context data array
     *
     * @var array
     */
    protected $data = [];

    protected ?array $logKeys = null;

    protected $config;

    protected $container = [];

    /**
     * Constructor to initialize the context.
     *
     * @param string     $env     The environment (default: Constants::ENV_DEV).
     * @param array      $data    The context data.
     * @param array|null $logKeys Optional log keys.
     */
    protected function __construct(string $env = Constants::ENV_DEV, array $data = [], ?array $logKeys = null)
    {
        $this->env = $env;
        $this->data = $data;
        $this->logKeys = $logKeys;
        $this->__init__();
    }

    /**
     * Initializes the context.
     *
     * This method should be overridden in subclasses to load environment-specific configurations.
     */
    protected function __init__()
    {
        if (! Constants::isValidEnv($this->env)) {
            throw new \InvalidArgumentException("Invalid environment: {$this->env}");
        }

        foreach (Constants::CONFIG_OVER_WRITE as $env) {
            $ds = DIRECTORY_SEPARATOR;
            $dir = 'config' . $ds . $env . $ds;

            $files = glob($dir . '*.php');

            foreach ($files as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                ConfigLoader::loadConfig($file, $name, 'a');
            }

            if ($this->env === $env) {
                break;
            }
        }
    }

    /**
     * Returns the Context instance.
     *
     * @param string     $env
     * @param array      $data
     * @param array|null $logKeys
     *
     * @return Context
     */
    public static function getInstance(string $env = Constants::ENV_DEV, array $data = [], ?array $logKeys = null): self
    {
        return new self($env, $data, $logKeys);
    }

    /**
     * Return the configs.
     *
     * @return ConfigLoader
     */
    public function getConfig()
    {
        if (isset($this->config)) {
            return $this->config;
        }

        // Load the configuration if not already set
        $this->config = ConfigLoader::getConfig('config');

        return $this->config;
    }

    /**
     * Set which keys to include in __toString/log output.
     *
     * @param  array $keys
     * @return void
     */
    public function setLogConfig(array $keys): void
    {
        $this->logKeys = $keys;
    }

    /**
     * Convert the context data to a string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->getValues());
    }

    /**
     * Get the values to be logged or returned.
     *
     * If logKeys are set, only those keys will be returned.
     * Otherwise, all context data will be returned.
     *
     * @return array
     */
    public function getValues()
    {
        if (is_null($this->logKeys)) {
            return $this->getData();
        }

        return array_intersect_key($this->getData(), array_flip($this->logKeys));
    }

    /**
     * Get the context data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get a value from the context data.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a value in the context data.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a key exists in the context data.
     *
     * @param  string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Remove a key from the context data.
     *
     * @param  string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Get all context data as array.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Provide custom debug info for var_dump().
     *
     * @return array
     */
    public function __debugInfo()
    {
        if (empty($this->logKeys)) {
            return $this->getValues();
        }

        return array_intersect_key($this->getValues(), array_flip($this->logKeys));
    }

    /**
     * Returns logger
     *
     * @param string $name
     *
     * @return \System\Core\Base\Log\Logger
     */
    public function getLogger(string $name = 'log')
    {
        return Container::get($name);
    }
}
