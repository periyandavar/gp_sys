<?php

namespace System\Core\Base\Module;

use Loader\Container;
use Loader\Load;
use Loader\Loader;
use System\Core\Base\Context\ConsoleContext;
use System\Core\Base\Context\WebContext;
use System\Core\Base\Log\Logger;
use System\Core\Utility;

class Module
{
    protected $context;
    protected string $name;
    protected $obj = [];
    protected Loader $loader;
    protected $container = [];
    protected ?Load $load = null;

    /**
     * Returns the context instance.
     *
     * @return WebContext|ConsoleContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Returns the load.
     *
     * @return Load
     */
    public function getLoad()
    {
        return $this->load;
    }

    /**
     * Return the service by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        if (! isset($this->container[$name])) {
            $this->container[$name] = Container::get($name);
        }

        return $this->container[$name];
    }

    /**
     * Constructor for the Module class.
     *
     * @param string $name
     */
    public function __construct(string $name = '')
    {
        $this->name = $name;
        $this->context = Utility::getContext();
        $this->load = new Load();
        Container::set('module', $this, true);
    }

    /**
     * Returns the logger instance.
     *
     * @param string $name
     *
     * @return Logger
     */
    public function getLogger(string $name = 'log'): Logger
    {
        return $this->get($name);
    }

    /**
     * Get the loader instance.
     *
     * @return Loader
     */
    public function getLoader()
    {
        if (! isset($this->loader)) {
            $this->loader = Loader::autoLoadClass($this, []);
        }

        return $this->loader;
    }

    /**
     * Magic method to get an object by name.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->obj[$name])) {
            return $this->obj[$name];
        }

        return null;
    }

    /**
     * Magic method to set an object by name.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->obj[$name] = $value;
    }

    /**
     * Magic method to check if an object is set.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->obj[$name]);
    }

    /**
     * Get the name of the module.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
