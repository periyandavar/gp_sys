<?php

namespace System\Core\Base\Log;

use Psr\Log\LoggerInterface;
use System\Core\Utility;

/**
 * @method info(string $message, array $context = [])
 * @method debug(string $message, array $context = [])
 * @method notice(string $message, array $context = [])
 * @method warning(string $message, array $context = [])
 * @method error(string $message, array $context = [])
 * @method critical(string $message, array $context = [])
 */
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

    protected LoggerInterface $handler;

    /**
     * Set the keys to ignore in the context.
     *
     * @param array $keys
     *
     * @return void
     */
    public function setIgnoreContextKeys(array $keys): void
    {
        $this->ignore_context_keys = $keys;
    }

    /**
     * Get the keys to ignore in the context.
     *
     * @return array
     */
    public function getIgnoreContextKeys(): array
    {
        return $this->ignore_context_keys;
    }

    /**
     * Get the context data, excluding ignored keys.
     *
     * @return array
     */
    private function getContext()
    {
        $context = Utility::getContext()->getValues();

        return array_filter($context, function($key) {
            return !in_array($key, $this->getIgnoreContextKeys());
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Frame the context data by merging it with the current context.
     *
     * @param mixed $context
     *
     * @return mixed
     */
    private function frameContextData($context)
    {
        if (! is_array($context)) {
            return $context;
        }

        return array_merge($context, $this->getContext());
    }

    /**
     * Logger constructor.
     *
     * @param LoggerInterface $logger
     * @param array           $ignore_context_keys
     */
    public function __construct(LoggerInterface $logger, array $ignore_context_keys = [])
    {
        $this->handler = $logger;
        $this->ignore_context_keys = $ignore_context_keys;
    }

    /**
     * Magic method to handle logging methods dynamically.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
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
