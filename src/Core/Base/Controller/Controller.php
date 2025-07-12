<?php

namespace System\Core\Base\Controller;

use Loader\Container;
use Loader\Load;
use Router\Request\Request;
use System\Core\Base\Context\WebContext;
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
     * Module class
     *
     * @var Module
     */
    protected $module;

    /**
     * @var Load
     */
    protected $load;

    protected $context;

    /**
     * Instantiate the Controller instance
     *
     */
    public function __construct()
    {
        $this->module = Container::get('module');
        $this->model = new Model();
        $this->service = new Service();
        $this->input = Container::get('request');
        $this->load = $this->module->load;
        $this->context = $this->module->getContext();
        // $this->context->getLogger()->info('The ' . static::class . ' class is initalized successfully');
    }

    /**
     * Returns the context.
     *
     * @return WebContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the model object
     *
     * @param Model $model
     *
     * @return void
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Set the service object
     *
     * @param Service $service
     *
     * @return void
     */
    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Return the model object
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Return the service object
     *
     * @return Service
     */
    public function getService(): Service
    {
        return $this->service;
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
