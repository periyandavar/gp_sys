<?php

namespace System\Core;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Loader;
use Logger\Log;
use Router\Route;
use Router\Router;

class BaseModule
{
    private $name;
    private $obj = [];
    private $loader = null;

    public function __construct($name)
    {
        Log::getInstance()->info('Initializing the Moudle class : ' . static::class);
        $this->name = $name;
        $this->addRoutes();
        $this->setUpServices();
        $this->setupAutoLoad();
        Container::set('module', $this);
    }

    public function run(string $url)
    {
        $url = parse_url($url)['path'] ?? '/';
        $url = ltrim($url, '/');
        $url = !empty($url) ? $url : '/';

        Log::getInstance()->info('Running the module to execute the route', ['path' => $url]);

        $result = Router::run(false, $url);
        Log::getInstance()->info('Routing success...!');

        return $result;
    }

    public function addRoutes()
    {
        $app_dir = defined("APP_DIR") ? APP_DIR : '';
        $routeFile = $app_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'module' . DIRECTORY_SEPARATOR . "{$this->name}" . DIRECTORY_SEPARATOR . 'routes.php';
        $routes = [];
        if (file_exists($routeFile)) {
            $routes = require_once $routeFile;
        }

        if (empty($routes)) {
            Log::getInstance()->info('No routes found, skipping loading routes...');

            return;
        }

        if (!is_array($routes)) {
            Log::getInstance()->info('Included the routes...');

            return;
        }

        // $prefix = str_replace('\\Module', '', static::class);
        $prefix = substr(static::class, 0, -(strlen('\\Module')));

        $prefix = $prefix . '\\Controller';
        Log::getInstance()->info('setting the prefix for routes', ['module' => $this->name, 'prefix' => $prefix]);
        foreach ($routes as $name => $route) {
            if ($route instanceof Route) {
                $route->setPrefix($prefix);
                if (empty($route->getName())) {
                    $name = $this->name . '.' . str_replace('/', '.', $route->getExpression());
                    $route->setName(strtolower($name));
                }
                Router::addRoute($route);
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

    public function setUpServices()
    {
        $app_dir = defined("APP_DIR") ? APP_DIR : '';
        $serviceFile = $app_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . "{$this->name}" . DIRECTORY_SEPARATOR . 'services.php';
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

        foreach ($services as $service) {
            Container::loadFromConfig($service);
        }

        Log::getInstance()->info('Services are loaded...');
    }

    public function setupAutoLoad()
    {
        $app_dir = defined(APP_DIR) ? APP_DIR : '';
        $autoloadFile = $app_dir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . "{$this->name}" . DIRECTORY_SEPARATOR . 'autoload.php';
        $autoloads = [];
        if (file_exists($autoloadFile)) {
            $autoloads = require_once $autoloadFile;
        }

        if (empty($autoloads)) {
            Log::getInstance()->info('No autoload found, skipping loading autoloads...');

            return;
        }

        $config = ConfigLoader::getInstance(ConfigLoader::VALUE_LOADER, [
            'model' => "App\\{$this->name}\\Model\\",
            'service' => "App\\{$this->name}\\Service\\",
            'helper' => 'App\\Helper\\',
            'library' => 'App\\Library\\',
        ])->load();
        $this->loader = Loader::autoLoadClass($this, $autoloads, $config);
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
}
