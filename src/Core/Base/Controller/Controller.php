<?php

namespace System\Core\Base\Controller;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Loader;
use Logger\Log;
use Router\Request\Request;
use System\Core\Base\Model\Model;
use System\Core\Base\Module\Module;
use System\Core\Base\Service\Service;

/**
 * Super class for all controller. All controllers should extend this controller
 * Controller class consists of basic level functions for various purposes
 *
 */
class Controller
{
    /**
     * Model class object that will has the link to the Model Class
     * using this variable we can acces the model class functions within this
     * controller Ex : $this->model->getData();
     *
     * @var Model $model
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
     * @var Service $service
     */
    protected $service;

    /**
     * Loader class object
     *
     * @var Loader
     */
    protected $loader;

    /**
     * Log class instance
     *
     * @var Log
     */
    protected $log;

    /**
     * Module class
     *
     * @var Module
     */
    protected $module;

    protected $config;
    protected $load;

    /**
     * Instantiate the Controller instance
     *
     */
    public function __construct()
    {
        $this->module = Container::get('module');
        $this->model = new Model();
        $this->service = new Service();
        $this->input = Container::get(Request::class);
        $this->config = ConfigLoader::getConfig('config');
        $this->loader = $this->module->getLoader();
        $this->load = $this->module->load;
        $this->log = Log::getInstance();
        $this->log->info(
            'The ' . static::class . ' class is initalized successfully'
        );
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function setService(Service $service)
    {
        $this->service = $service;
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
        return isset($this->module->$name);
    }
}
