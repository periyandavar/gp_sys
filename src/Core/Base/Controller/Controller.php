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
}
