<?php

namespace System\Core;

use Error;
use Exception;
use System\Core\Exception\ConsoleException;

abstract class Console
{
    protected static string $name = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];
    protected array $args = [];

    public const KEY_SHORT = 'short';
    public const KEY_MESSAGE = 'message';
    public const KEY_DEFAULT = 'default';

    /**
     * Define short and long options for getopt().
     * Example:
     *   return [
     *     'short' => 'hv::',
     *     'long' => ['help', 'version::']
     *   ];
     */
    /**
     * Define short and long options for getopt().
     */
    protected function options(): array
    {
        return [
            'help' => [
                'short' => 'h',
                self::KEY_MESSAGE => 'prints help message'
            ]
        ];
    }

    public function execute()
    {
        try {
            $this->run();
        } catch (Exception $e) {
            $this->showError($e->getMessage());
            throw new ConsoleException('Error executing command: ' . $this->getName(), $e->getCode(), $e);
        } catch (Error $e) {
            $this->showError($e->getMessage());
            throw new ConsoleException('Error executing command: ' . $this->getName(), $e->getCode(), $e);
        } catch (\Throwable $e) {
            $this->showError($e->getMessage());
            throw new ConsoleException('Error executing command: ' . $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * Entry point for the command execution.
     */
    abstract public function run(): void;

    public function __construct()
    {
        global $argv;
        $this->args = $argv ?? [];
        $this->parseArguments();
    }

    protected function parseArguments()
    {
        $this->options = [];
        $this->arguments = [];

        $args = $this->args;
        array_shift($args); // Remove script name

        [$shortMap, $longMap] = $this->buildOptionMaps($this->options());

        $this->parseArgsLoop($args, $this->options(), $shortMap, $longMap);
        $this->applyDefaultOptions($this->options(), $shortMap);
    }

    private function buildOptionMaps(array $optionsDef): array
    {
        $shortMap = [];
        $longMap = [];
        foreach ($optionsDef as $long => $opt) {
            if (!empty($opt[self::KEY_SHORT])) {
                $shortMap[rtrim($opt[self::KEY_SHORT], ':')] = $long;
            }
            $longMap[$long] = $long;
        }

        return [$shortMap, $longMap];
    }

    private function parseArgsLoop(array $args, array $optionsDef, array $shortMap, array $longMap): void
    {
        $i = 0;
        while ($i < count($args)) {
            $arg = $args[$i];
            if (substr($arg, 0, 2) === '--') {
                $this->parseLongOption($arg, $longMap);
                $i++;
            } elseif (substr($arg, 0, 1) === '-') {
                $i += $this->parseShortOption($args, $i, $optionsDef, $shortMap);
            } else {
                $this->arguments[] = $arg;
                $i++;
            }
        }
    }

    private function parseLongOption(string $arg, array $longMap): void
    {
        $eqPos = strpos($arg, '=');
        if ($eqPos !== false) {
            $name = substr($arg, 2, $eqPos - 2);
            $value = substr($arg, $eqPos + 1);
        } else {
            $name = substr($arg, 2);
            $value = true;
        }
        if (isset($longMap[$name])) {
            $this->options[$name] = $value;
        }
    }

    private function parseShortOption(array $args, int $i, array $optionsDef, array $shortMap): int
    {
        $arg = $args[$i];
        $name = substr($arg, 1, 1);
        $value = true;
        $def = $optionsDef[$shortMap[$name] ?? ''] ?? null;
        $consumed = 1;
        if ($def && !empty($def[self::KEY_SHORT]) && substr($def[self::KEY_SHORT], -1) === ':') {
            if (strlen($arg) > 2 && $arg[2] === '=') {
                $value = substr($arg, 3);
            } elseif (isset($args[$i + 1]) && strpos($args[$i + 1], '-') !== 0) {
                $value = $args[$i + 1];
                $consumed = 2;
            }
        }
        if (isset($shortMap[$name])) {
            $this->options[$name] = $value;
            $this->options[$shortMap[$name]] = $value;
        }

        return $consumed;
    }

    private function applyDefaultOptions(array $optionsDef, array $shortMap): void
    {
        foreach ($optionsDef as $long => $opt) {
            if (!array_key_exists($long, $this->options) && isset($opt[self::KEY_DEFAULT])) {
                $this->options[$long] = $opt[self::KEY_DEFAULT];
            }
            if (!empty($opt[self::KEY_SHORT])) {
                $short = rtrim($opt[self::KEY_SHORT], ':');
                if (!array_key_exists($short, $this->options) && isset($opt[self::KEY_DEFAULT])) {
                    $this->options[$short] = $opt[self::KEY_DEFAULT];
                }
            }
        }
    }
    /**
     * Extracts positional arguments from $argv.
     */
    protected function extractPositionalArguments(array $parsedOptions): array
    {
        $argv = $this->args;
        
        // Remove options and their values
        foreach ($argv as $index => $arg) {
            if (strpos($arg, '-') === 0) {
                unset($argv[$index]);
                // If the option has a value, remove the next argument
                $optionName = ltrim($arg, '-');
                if (isset($parsedOptions[$optionName]) && $parsedOptions[$optionName] !== false) {
                    unset($argv[$index + 1]);
                }
            }
        }

        // Reindex array
        return array_values($argv);
    }

    /**
     * Retrieves the value of a specific option.
     */
    public function getOption(string $key): mixed
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        $options = $this->options();
        $opt = $options[$key] ?? $options[$key . ':'] ?? null;
        if (isset($opt)) {
            $short_name = $opt[self::KEY_SHORT] ?? null;
            if ($short_name) {
                $short_name = rtrim($short_name, ':');

                return $this->options[$short_name] ?? null;
            }
        }

        foreach ($options as $opt => $option) {
            $short_name = rtrim($option[self::KEY_SHORT] ?? '', ':');
            if ($short_name === $key) {
                $opt = rtrim($opt, ':');

                return $this->options[$opt] ?? null;
            }
        }

        return null;
    }

    /**
     * Retrieves the value of a specific positional argument.
     */
    public function getArgument(int $index): mixed
    {
        return $this->arguments[$index] ?? null;
    }

    /**
     * Displays help information for the command.
     */
    public function displayHelp(): void
    {
        $help = $this->getHelp();

        $this->showMessage($help);
    }

    public function handleHelp()
    {
        if ($this->getOption('h')) {
            $this->displayHelp();

            return;
        }
    }

    public function getHelp()
    {
        $msg = '';
        $msg .= "Command: {$this->getName()}\n";
        $msg .= "Description: {$this->description}\n";
        $command = static::class;
        $msg .= "Usage: php {$command}.php [options]\n";
        $msg .= $this->getCmdHelp();

        return $msg;
    }

    public function getCmdHelp()
    {
        $msg = "Options:\n";
        foreach ($this->options() as $long => $opt) {
            $command_help = '  ';
            if (!empty($opt[self::KEY_SHORT])) {
                $command_help .= '-' . $opt[self::KEY_SHORT] . ', ';
            }
            $command_help .= '--' . $long . "\t" . ($opt[self::KEY_MESSAGE] ?? '') . "\n";
            $msg .= $command_help;
        }

        return $msg;
    }

    /**
     * Display a colored message in the console.
     * Usage: $this->displayMessage('Warning!', 'warning');
     */
    public function showMessage(string $message, string $type = 'default'): void
    {
        $colors = [
            'default' => "\033[1;37m", // White
            'info' => "\033[1;34m", // Blue
            'success' => "\033[1;32m", // Green
            'warning' => "\033[1;33m", // Yellow
            'error' => "\033[1;31m", // Red
            'loading' => "\033[1;36m", // Cyan
            'running' => "\033[1;35m", // Magenta
            'reset' => "\033[0m",    // Reset
        ];

        $color = $colors[$type] ?? $colors['info'];
        $reset = $colors['reset'];
        echo "{$color}{$message}{$reset}\n";
    }

    public function showError(string $message)
    {
        $this->showMessage($message, 'error');
    }

    public function showWarning(string $message)
    {
        $this->showMessage($message, 'warning');
    }

    public function showInfo(string $message)
    {
        $this->showMessage($message, 'info');
    }

    public function showSuccess(string $message)
    {
        $this->showMessage($message, 'success');
    }

    public function showLoading(string $message)
    {
        $this->showMessage($message, 'loading');
    }

    public function error(string $message, $code = 0, $exception = null)
    {
        throw new Exception($message, $code, $exception);
    }

    public static function getName(): string
    {
        return static::$name;
    }

    public static function getBuildInCommands(): array
    {
        return require_once __DIR__ . '/Command/commands.php';
    }

    public static function isBuildInCommand(string $name): bool
    {
        $name = self::formatCommandName($name);
        $commands = self::getBuildInCommands();

        return isset($commands[$name]);
    }

    public static function getCommandAction(string $name): ?string
    {
        $name = self::formatCommandName($name);
        $commands = self::getBuildInCommands();

        return $commands[$name] ?? null;
    }

    public static function formatCommandName(string $name): string
    {
        $name = explode(':', $name);

        return reset($name);
    }
}
