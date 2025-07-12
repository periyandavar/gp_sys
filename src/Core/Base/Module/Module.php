<?php

namespace System\Core\Base\Module;

use Loader\Container;
use Loader\Load;
use Loader\Loader;
use Logger\Log;
use Router\Route;
use Router\Router;
use Router\Wrapper;

class Module
{
    private $name;
    private $obj = [];
    private $loader = null;

    private $base_path = '';

    public Load $load;

    public function __construct($name)
    {
        Log::getInstance()->info('Initializing the Moudle class : ' . static::class);
        $this->name = $name;
        $this->load = new Load();
        $app_dir = defined('APP_DIR') ? APP_DIR : '';
        $module_folder = ucfirst($this->name);
        $this->base_path = $app_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . "{$module_folder}" . DIRECTORY_SEPARATOR ;
        $this->addRoutes();
        $this->setUpServices();
        $this->setupAutoLoad();
        Container::set('module', $this, true);
    }

    public function run(string $url)
    {
        $url = parse_url($url)['path'] ?? '/';

        Log::getInstance()->info('Running the module to execute the route', ['path' => $url]);

        $result = Router::run(false, $url);
        Log::getInstance()->info('Routing success...!');

        return $result;
    }

    public function addRoutes()
    {
        $routeFile = $this->base_path . 'routes.php';
        $routes = [];
        if (file_exists($routeFile)) {
            $routes = require_once $routeFile;
        }

        if (!is_array($routes)) {
            Log::getInstance()->info('Included the routes...');

            return;
        }

        if (empty($routes)) {
            Log::getInstance()->info('No routes found, skipping loading routes...' . $routeFile);

            return;
        }

        $class_name = static::class;
        $prefix = '';
        if ($class_name != Module::class) {
            $prefix = substr($class_name, 0, -(strlen('\\Module'))) . '\\Controller';
        }

        Log::getInstance()->info('setting the prefix for routes', ['module' => $this->name, 'prefix' => $prefix]);
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

    private function setRoute(Route $route, $prefix = '')
    {
        $route->setPrefix($prefix);
        if (empty($route->getName())) {
            $name = $this->name . '.' . str_replace('/', '.', $route->getExpression());
            $route->setName(strtolower($name));
        }

        Router::addRoute($route);
    }

    public function setUpServices()
    {
        $serviceFile = $this->base_path . 'services.php';
        $services = [];
        if (file_exists($serviceFile)) {
            $services = require_once $serviceFile;
        }

        if (empty($services)) {
            Log::getInstance()->info('No services found, skipping loading services...');

            return;
        }

        if (!is_array($services)) {
            Log::getInstance()->info('Services are included...');

            return;
        }
        Container::loadFromConfig($services);

        Log::getInstance()->info('Services are loaded...');
    }

    public function setupAutoLoad()
    {
        $autoloadFile = $this->base_path . 'autoloads.php';
        $autoloads = [];

        if (file_exists($autoloadFile)) {
            $autoloads = require_once $autoloadFile;
        }

        if (empty($autoloads)) {
            Log::getInstance()->info('No autoload found, skipping loading autoloads...');

            return;
        }
        $this->loader = Loader::autoLoadClass($this, $autoloads);

        Log::getInstance()->info('Autoloaded the class...', ['autoload' => $autoloads]);
    }

    public function getLoader()
    {
        if (! isset($this->loader)) {
            $this->loader = Loader::autoLoadClass($this, []);
        }

        return $this->loader;
    }

    public function __get($name)
    {
        if (isset($this->obj[$name])) {
            return $this->obj[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->obj[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->obj[$name]);
    }

    public function getBasePath()
    {
        return $this->base_path;
    }

    public function getName()
    {
        return $this->name;
    }
}
