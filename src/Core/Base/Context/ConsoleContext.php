<?php

namespace System\Core\Base\Context;

use Loader\Config\ConfigLoader;
use System\Core\Constants;

class ConsoleContext extends Context
{
    protected $command;
    protected $action;
    protected $args = [];

    protected function __init__()
    {
        parent::__init__();
        $ds = DIRECTORY_SEPARATOR;
        $config = __DIR__ . $ds . 'console' . $ds . 'config.php' ;
        if (file_exists($config)) {
            ConfigLoader::loadConfig($config, 'config', 'a');
        }

        $this->command = $this->data['command'] ?? null;
        $this->action = $this->data['action'] ?? null;
        $this->args = $this->data['args'] ?? [];
        unset($this->data['command'], $this->data['action'], $this->data['args']);
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getArgv(): array
    {
        global $argv;

        return $argv ?? [];
    }

    public function getArgc(): int
    {
        global $argc;

        return $argc ?? 0;
    }

    public function getScriptName(): ?string
    {
        global $argv;

        return $argv[0] ?? null;
    }

    public function getOption(string $name, $default = null)
    {
        global $argv;
        foreach ($argv as $arg) {
            if (strpos($arg, "--$name=") === 0) {
                return substr($arg, strlen($name) + 3);
            }
        }

        return $default;
    }

    public function isInteractive(): bool
    {
        return function_exists('posix_isatty') && posix_isatty(STDIN);
    }

    public function getData(): array
    {
        $contextData = $this->all();
        $consoleData = [
            'command' => $this->getCommand(),
            'action' => $this->getAction(),
            'args' => $this->getArgs(),
            'argv' => $this->getArgv(),
            'argc' => $this->getArgc(),
            'script_name' => $this->getScriptName(),
            'is_interactive' => $this->isInteractive(),
        ];

        return array_merge($contextData, $consoleData);
    }

    public static function getInstance(string $env = Constants::ENV_DEV, array $data = [], ?array $logKeys = null): self
    {
        $command = $data['command'] ?? null;
        $action = $data['action'] ?? null;
        $args = $data['args'] ?? [];
        if ($command === null || $action === null) {
            throw new \InvalidArgumentException('Command and action must be provided.');
        }

        return new self($env, $data, $logKeys);
    }
}
