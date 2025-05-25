<?php

namespace System\Core;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Loader;
use Logger\Log;
use Router\Request\Request;

/**
 * Super class for all controller. All controllers should extend this controller
 * SysController class consists of basic level functions for various purposes
 *
 */
class SysController
{
    /**
     * Model class object that will has the link to the Model Class
     * using this variable we can acces the model class functions within this
     * controller Ex : $this->model->getData();
     *
     * @var BaseModel $model
     */
    protected $model;

    /**
     * Input allows us to access the get, post, session, files values
     *
     * @var Request $input
     */
    protected $input;

    /**
     * Service class object that will offers the services(bussiness logics)
     *
     * @var BaseService $service
     */
    protected $service;

    /**
     * Loader class object
     *
     * @var Loader
     */
    protected $load;

    /**
     * Log class instance
     *
     * @var Log
     */
    protected $log;

    /**
     * Module class
     *
     * @var BaseModule
     */
    protected $module;

    protected $config;

    /**
     * Instantiate the SysController instance
     *
     * @param BaseModel   $model   model class object to intialize $this->model
     * @param BaseService $service service class object to intialize $this->service
     */
    public function __construct($model = null, $service = null)
    {
        $this->module = Container::get('module');
        $this->model = $model;
        $this->service = $service;
        $this->input = Container::get(Request::class);
        $this->config = ConfigLoader::getConfig('config');
        $this->load = $this->module->getLoader();
        $this->log = Log::getInstance();
        $this->log->info(
            'The ' . static::class . ' class is initalized successfully'
        );
    }

    /**
     * This function will call when the undefined function is called
     *
     * @param string $name function name
     * @param array  $args arguments
     *
     * @return void
     */
    public function __call(string $name, array $args)
    {
        $this->log->error("Undefined method call in $name " . get_called_class());
    }

    /**
     * This function will call when the undefined static function is called
     *
     * @param string $name function name
     * @param array  $args arguments
     *
     * @return void
     */
    public static function __callStatic($name, $args)
    {
        Log::getInstance()->error('Undefined static method call in ' . get_called_class());
    }

    // /**
    //  * Making clone as deep copy instead of shallow
    //  *
    //  * @return void
    //  */
    // public function __clone()
    // {
    //     $this->model = clone $this->model;
    //     $this->service = clone $this->service;
    // }

    /**
     * Add new object to $_obj array
     *
     * @param string $name  name
     * @param mixed  $value object
     *
     * @return void
     */
    final public function __set(string $name, $value)
    {
        // $this->_obj[$name] = $value;
        $this->module->$name = $value;
    }

    /**
     * Get the object
     *
     * @param string $name object name
     *
     * @return mixed
     */
    final public function __get($name)
    {
        // if (array_key_exists($name, $this->_obj)) {
        //     return $this->_obj[$name];
        // }
        // return null;
        return $this->module->$name;
    }

    /**
     * Check the object is present or not
     *
     * @param string $name object name
     *
     * @return bool
     */
    final public function __isset(string $name): bool
    {
        // return array_key_exists($name, $this->_obj);
        return isset($this->module->$name);
    }
}
