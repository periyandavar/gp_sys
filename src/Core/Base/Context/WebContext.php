<?php

namespace System\Core\Base\Context;

use Loader\Config\ConfigLoader;
use Loader\Container;
use Router\Route;
use System\Core\Constants;

class WebContext extends Context
{
    public const PARAM_URI = 'uri';
    protected $uri;
    protected $router = '';
    protected $module = '';

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

    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    public function setRouter(string $router): void
    {
        $this->router = $router;
    }

    public function getRouter(): string
    {
        return $this->router;
    }

    public function getRoute(): ?Route
    {
        return Container::get('route', null, true);
    }

    public function getController(): string
    {
        if (! $this->getRoute()) {
            return '';
        }

        return $this->getRoute()->getExpression();
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getUrlParams(): array
    {
        if (! $this->getRoute()) {
            return [];
        }

        return $this->getRoute()->getUrlParams();
    }

    public function getRequestMethod(): ?string
    {
        return $_SERVER['REQUEST_METHOD'] ?? null;
    }

    public function getRequestUri(): ?string
    {
        return $_SERVER['REQUEST_URI'] ?? null;
    }

    public function getQueryParams(): array
    {
        return $_GET;
    }

    public function getPostParams(): array
    {
        return $_POST;
    }

    public function getSession(): array
    {
        return $_SESSION ?? [];
    }

    public function getCookies(): array
    {
        return $_COOKIE ?? [];
    }
    public function getInputData(): array
    {
        return (array) json_decode(file_get_contents('php://input'), true);
    }

    public function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function getData(): array
    {
        $contextData = $this->all();
        $webData = $this->getAPIData();

        return array_merge($contextData, $webData);
    }

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

    public static function getInstance(string $env = Constants::ENV_DEV, array $data = [], ?array $logKeys = null): self
    {
        $uri = $data['uri'] ?? null;
        if ($uri === null) {
            throw new \InvalidArgumentException('Router and module must be provided.');
        }

        return new self($env, $data, $logKeys);
    }
}
