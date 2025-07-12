<?php

namespace System\Core;

use Exception;
use Loader\Container;
use System\Core\Base\Context\ConsoleContext;
use System\Core\Base\Module\ConsoleModule;
use System\Core\Exception\ConsoleException;

abstract class Console
{
    protected $context;
    protected $module;
    protected $command;
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
    public function options(): array
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
        } catch (\Throwable $e) {
            $this->showError($e->getMessage());
            throw new ConsoleException('Error executing command: ' . $this->getName() . " : {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * Entry point for the command execution.
     */
    abstract public function run(): void;

    public function __construct()
    {
        $this->context = $this->get('context');
        $this->command = $this->getContext()->getCommand();
        $this->module = new ConsoleModule($this->command);
        $this->args = $this->getContext()->getArgs();
        $this->parseArguments();
    }

    /**
     * Returns the context instance.
     *
     * @return ConsoleModule
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Returns the command name.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * parse the command line arguments.
     */
    protected function parseArguments()
    {
        $this->options = [];
        $this->arguments = [];

        $args = $this->args;

        [$shortMap, $longMap] = $this->buildOptionMaps($this->options());

        $this->parseArgsLoop($args, $this->options(), $shortMap, $longMap);
        $this->applyDefaultOptions($this->options(), $shortMap);
    }

    /**
     * Builds short and long option maps from the options definition.
     *
     * @param array $optionsDef
     *
     * @return array
     */
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

    /**
     * Parses the command line arguments and populates options and arguments.
     *
     * @param array $args
     * @param array $optionsDef
     * @param array $shortMap
     * @param array $longMap
     *
     * @return void
     */
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

    /**
     * Parses a long option from the command line arguments.
     *
     * @param string $arg
     * @param array  $longMap
     *
     * @return void
     */
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

    /**
     * Parses a short option from the command line arguments.
     *
     * @param array $args
     * @param int   $i
     * @param array $optionsDef
     * @param array $shortMap
     *
     * @return int
     */
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

    /**
     * Applies default options to the command line arguments.
     *
     * @param array $optionsDef
     * @param array $shortMap
     *
     * @return void
     */
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
     *
     * @param array $parsedOptions
     *
     * @return array
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
     *
     * @param string $key
     *
     * @return mixed
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
     *
     * @param int $index
     *
     * @return mixed
     */
    public function getArgument(int $index): mixed
    {
        return $this->arguments[$index] ?? null;
    }

    /**
     * Displays help information for the command.
     *
     * @return void
     */
    public function displayHelp(): void
    {
        $help = $this->getHelp();

        $this->showMessage($help);
    }

    /**
     * Handles the help command.
     * If the 'h' option is set, it displays the help information.
     *
     * @return void
     */
    public function handleHelp()
    {
        if ($this->getOption('h')) {
            $this->displayHelp();

            return;
        }
    }

    /**
     * Returns the help message for the command.
     *
     * @return string
     */
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

    /**
     * Returns the help message for the command options.
     *
     * @return string
     */
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

    /**
     * Show an error message and throw an exception.
     *
     * @param string $message
     *
     * @throws Exception
     */
    public function showError(string $message)
    {
        $this->showMessage($message, 'error');
    }

    /**
     * Show a warning message.
     *
     * @param string $message
     *
     * @return void
     */
    public function showWarning(string $message)
    {
        $this->showMessage($message, 'warning');
    }

    /**
     * Show an info message.
     *
     * @param string $message
     *
     * @return void
     */
    public function showInfo(string $message)
    {
        $this->showMessage($message, 'info');
    }

    /**
     * Show a success message.
     *
     * @param string $message
     *
     * @return void
     */
    public function showSuccess(string $message)
    {
        $this->showMessage($message, 'success');
    }

    /**
     * Show a loading message.
     *
     * @param string $message
     *
     * @return void
     */
    public function showLoading(string $message)
    {
        $this->showMessage($message, 'loading');
    }

    /**
     * Thorw error with a message.
     * @param  string     $message
     * @param  mixed      $code
     * @param  mixed      $exception
     * @throws \Exception
     *
     * @return never
     */
    public function error(string $message, $code = 0, $exception = null)
    {
        throw new Exception($message, $code, $exception);
    }

    /**
     * Returns the name of the command.
     *
     * @return string
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * Get build-in commands.
     *
     * @return array
     */
    public static function getBuildInCommands(): array
    {
        static $commands = require_once __DIR__ . '/Command/commands.php';

        return $commands;
    }

    /**
     * Check if the command is a build-in command.
     *
     * @param string $cname The command name.
     *
     * @return bool
     */
    public static function isBuildInCommand(string $cname): bool
    {
        $name = self::formatCommandName($cname);
        $commands = self::getBuildInCommands();

        $command = $commands[$name] ?? null;

        if ($command === null) {
            return false;
        }

        return $command::isValidSubCommand($cname);
    }

    /**
     * Check if the command is a valid sub-command.
     *
     * @param string $name The command name.
     *
     * @return bool
     */
    public static function isValidSubCommand(string $name)
    {
        return count(explode(':', $name)) === 1;
    }

    /**
     * Get the action name for a command.
     *
     * @param string $name The command name.
     *
     * @return string|null
     */
    public static function getCommandAction(string $name): ?string
    {
        if (! self::isBuildInCommand($name)) {
            return null;
        }
        $name = self::formatCommandName($name);
        $commands = self::getBuildInCommands();

        return $commands[$name] ?? null;
    }

    /**
     * Format the command name to remove any sub-command parts.
     *
     * @param string $name The command name.
     *
     * @return string
     */
    public static function formatCommandName(string $name): string
    {
        $name = explode(':', $name);

        return reset($name);
    }

    /**
     * Get the context instance.
     *
     * @return ConsoleContext
     */
    protected function getContext()
    {
        return $this->context;
    }

    /**
     * Get an object from the container.
     *
     * @param string $name The name of the object.
     *
     * @return mixed
     */
    protected function get($name)
    {
        return Container::get($name);
    }

    /**
     * Get the logger instance.
     *
     * @param string $name The name of the logger.
     *
     * @return mixed
     */
    protected function getLogger($name = 'log')
    {
        return $this->get($name);
    }

    /**
     * Get the configuration instance.
     *
     * @return mixed
     */
    protected function getConfig()
    {
        return $this->getContext()->getConfig();
    }
}
