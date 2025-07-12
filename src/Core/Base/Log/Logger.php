<?php

namespace System\Core\Base\Log;

use Logger\Logger as LogHandler;
use Psr\Log\LoggerInterface;
use System\Core\Utility;

class Logger
{
    public const LOG_HANDLING_METHODS = [
        'alert',
        'critical',
        'debug',
        'emergency',
        'error',
        'info',
        'notice',
        'warning',
        'log',
    ];

    private $ignore_context_keys = [];

    protected $handler;

    public function setIgnoreContextKeys(array $keys): void
    {
        $this->ignore_context_keys = $keys;
    }

    public function getIgnoreContextKeys(): array
    {
        return $this->ignore_context_keys;
    }

    private function getContext()
    {
        $context = Utility::getContext()->getValues();

        return array_filter($context, function($key) {
            return !in_array($key, $this->getIgnoreContextKeys());
        }, ARRAY_FILTER_USE_KEY);
    }

    private function frameContextData($context)
    {
        if (! is_array($context)) {
            return $context;
        }

        return array_merge($context, $this->getContext());
    }

    public function __construct(LoggerInterface $logger, array $ignore_context_keys = [])
    {
        $this->handler = new LogHandler($logger);
        $this->ignore_context_keys = $ignore_context_keys;
    }

    public function __call($method, $args)
    {
        if (in_array($method, self::LOG_HANDLING_METHODS)) {
            if (isset($args[1])) {
                $args[1] = $this->frameContextData($args[1]);
            }

            if (count($args) === 1) {
                $args[1] = $this->frameContextData([]);
            }
        }

        return call_user_func_array([$this->handler, $method], $args);
    }
}
