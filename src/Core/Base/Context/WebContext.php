<?php

namespace System\Core\Base\Context;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Loader\Exception\LoaderException;
use Router\Route;
use System\Core\Constants;

class WebContext extends Context
{
    public const PARAM_URI = 'uri';
    protected $uri;
    protected $router = '';
    protected $module = '';

    /**
     * Initializes the web context.
     */
    protected function __init__()
    {
        parent::__init__();
        $ds = DIRECTORY_SEPARATOR;
        $config = __DIR__ . $ds . 'src' . $ds . 'config.php';
        if (file_exists($config)) {
            ConfigLoader::loadConfig($config, 'config', 'a');
        }

        $this->uri = $this->data['uri'] ?? null;
        unset($this->data['uri']);
    }

    /**
     * Set the module name.
     *
     * @param string $module
     *
     * @return void
     */
    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    /**
     * Set the router.
     *
     * @param string $router
     *
     * @return void
     */
    public function setRouter(string $router): void
    {
        $this->router = $router;
    }

    /**
     * Get the URI.
     *
     * @return string
     */
    public function getRouter(): string
    {
        return $this->router;
    }

    /**
     * Get the route object.
     *
     * @return Route|null
     */
    public function getRoute(): ?Route
    {
        try {
            return Container::get('route');
        } catch (LoaderException $e) {
            return null;
        }
    }

    /**
     * Return the expression of the route.
     *
     * @return string
     */
    public function getController(): string
    {
        if (! $this->getRoute()) {
            return '';
        }

        return $this->getRoute()->getExpression();
    }

    /**
     * Get the module name.
     *
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Get the url parameters from the route.
     *
     * @return array
     */
    public function getUrlParams(): array
    {
        if (! $this->getRoute()) {
            return [];
        }

        return $this->getRoute()->getUrlParams();
    }

    /**
     * Get the request method.
     *
     * @return string|null
     */
    public function getRequestMethod(): ?string
    {
        return $_SERVER['REQUEST_METHOD'] ?? null;
    }

    /**
     * Get the request URI.
     *
     * @return string|null
     */
    public function getRequestUri(): ?string
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }

    /**
     * Gets the query parameters.
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $_GET;
    }

    /**
     * Gets the post parameters.
     *
     * @return array
     */
    public function getPostParams(): array
    {
        return $_POST;
    }

    /**
     * Gets the session data.
     *
     * @return array
     */
    public function getSession(): array
    {
        return $_SESSION ?? [];
    }

    /**
     * Gets the cookies.
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $_COOKIE;
    }

    /**
     * Gets the input data from the request body.
     *
     * @return array
     */
    public function getInputData(): array
    {
        return (array) json_decode(file_get_contents('php://input'), true);
    }

    /**
     * Checks if the request is an AJAX request.
     *
     * @return bool
     */
    public function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get the raw json data.
     *
     * @return array
     */
    public function getData(): array
    {
        $contextData = $this->all();
        $webData = $this->getAPIData();

        return array_merge($contextData, $webData);
    }

    /**
     * Get the API data.
     *
     * @return array
     */
    public function getAPIData()
    {
        return [
            'router' => $this->getRouter(),
            'controller' => $this->getController(),
            'module' => $this->getModule(),
            'url_params' => $this->getUrlParams(),
            'request_method' => $this->getRequestMethod(),
            'request_uri' => $this->getRequestUri(),
            'query_params' => $this->getQueryParams(),
            'post_params' => $this->getPostParams(),
            'session' => $this->getSession(),
            'is_ajax' => $this->isAjaxRequest(),
            'input_data' => $this->getInputData(),
            'cookies' => $this->getCookies(),
        ];
    }

    /**
     * Get the instance of the WebContext.
     *
     * @param string     $env
     * @param array      $data
     * @param array|null $logKeys
     *
     * @return self
     */
    public static function getInstance(string $env = Constants::ENV_DEV, array $data = [], ?array $logKeys = null): self
    {
        $uri = $data['uri'] ?? null;
        if ($uri === null) {
            throw new \InvalidArgumentException('Router and module must be provided.');
        }

        return new self($env, $data, $logKeys);
    }
}
