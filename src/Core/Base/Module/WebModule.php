<?php

namespace System\Core\Base\Module;

use Loader\Container;
use Loader\Loader;
use Router\Route;
use Router\Router;
use Router\Wrapper;
use System\Core\Base\Context\WebContext;

class WebModule extends Module
{
    protected string $base_path;

    /**
     * Constructor for the Module class.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $module_folder = ucfirst($name);
        $app_dir = defined('APP_DIR') ? APP_DIR : '';
        $this->base_path = $app_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . "{$module_folder}" . DIRECTORY_SEPARATOR ;
        parent::__construct($name);
        $this->addRoutes();
        $this->setUpServices();
        $this->setupAutoLoad();
    }

    /**
     * Run the module to execute the route.
     *
     * @param string $url
     */
    public function run(string $url)
    {
        $url = parse_url($url)['path'] ?? '/';

        $this->getLogger()->info('Running the module to execute the route', ['path' => $url]);

        $this->context->setModule($this->name);
        $this->context->setRouter($url);

        $result = Router::run(false, $url);
        $this->getLogger()->info('Routing success...!');

        return $result;
    }

    /**
     * Add routes for the module.
     *
     * @return void
     */
    public function addRoutes()
    {
        $routeFile = $this->base_path . 'routes.php';
        $routes = [];
        if (file_exists($routeFile)) {
            $routes = require_once $routeFile;
        }

        if (!is_array($routes)) {
            $this->getLogger()->info('Included the routes...');

            return;
        }

        if (empty($routes)) {
            $this->getLogger()->info('No routes found, skipping loading routes...' . $routeFile);

            return;
        }

        $class_name = static::class;
        $prefix = '';
        if ($class_name != Module::class) {
            $prefix = substr($class_name, 0, -(strlen('\\Module'))) . '\\Controller';
        }

        $this->getLogger()->info('setting the prefix for routes', ['module' => $this->name, 'prefix' => $prefix]);
        foreach ($routes as $name => $route) {
            if ($route instanceof Route) {
                $this->setRoute($route, $prefix);
                continue;
            }

            if ($route instanceof Wrapper) {
                $route = $route->getRoutes();
                foreach ($route as $r) {
                    $this->setRoute($r, $prefix);
                }
                continue;
            }

            if (is_array($route)) {
                $rule = $route[0] ?? $route['rule'] ?? '';
                $expression = $route[1] ?? $route['expression'] ?? '';
                $method = $route[2] ?? $route['method'] ?? Router::METHOD_GET;
                $filter = $route[3] ?? $route['filter'] ?? [];
                $name = $route[4] ?? $route['name'] ?? !is_numeric($name) ? $name : strtolower($this->name . '.' . str_replace('/', '.', $expression));
                if (empty($rule) || empty($expression)) {
                    continue;
                }
                $route = (new Route($rule, $expression, $method, $filter, $name))->setPrefix($prefix);
                Router::addRoute($route);
            }
        }
    }

    /**
     * Set up services for the module.
     *
     * @return void
     */
    public function setUpServices()
    {
        $serviceFile = $this->base_path . 'services.php';
        $services = [];
        if (file_exists($serviceFile)) {
            $services = require_once $serviceFile;
        }

        if (empty($services)) {
            $this->getLogger()->info('No services found, skipping loading services...');

            return;
        }

        if (!is_array($services)) {
            $this->getLogger()->info('Services are included...');

            return;
        }
        Container::loadFromConfig($services);

        $this->getLogger()->info('Services are loaded...');
    }

    /**
     * Setup autoload for the module.
     *
     * @return void
     */
    public function setupAutoLoad()
    {
        $autoloadFile = $this->base_path . 'autoloads.php';
        $autoloads = [];

        if (file_exists($autoloadFile)) {
            $autoloads = require_once $autoloadFile;
        }

        if (empty($autoloads)) {
            $this->getLogger()->info('No autoload found, skipping loading autoloads...');

            return;
        }
        $this->loader = Loader::autoLoadClass($this, $autoloads);

        $this->getLogger()->info('Autoloaded the class...', ['autoload' => $autoloads]);
    }

    /**
     * Set the route with a prefix.
     *
     * @param Route $route
     *
     * @return void
     */
    private function setRoute(Route $route, $prefix = '')
    {
        $route->setPrefix($prefix);
        if (empty($route->getName())) {
            $name = $this->name . '.' . str_replace('/', '.', $route->getExpression());
            $route->setName(strtolower($name));
        }

        Router::addRoute($route);
    }

    /**
     * Get the base path of the module.
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * Returns the context instance.
     *
     * @return WebContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
