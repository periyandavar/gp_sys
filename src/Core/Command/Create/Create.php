<?php

namespace System\Core\Command\Create;

use System\Core\Console;

class Create extends Console
{
    protected static string $name = 'create';
    protected string $description = 'Create a new migration or other stuff';

    protected string $template_dir = __DIR__ . '/templates/';

    /**
     * Define short and long options for getopt().
     */
    public function options(): array
    {
        return [
            'help' => [
                'short' => 'h',
                'message' => 'prints help message'
            ],
        ];
    }

    /**
     * Entry point for the command execution.
     */
    public function run(): void
    {
        $command = $this->getCommand();

        $action = str_replace(static::$name . ':', '', $command);
        $name = reset($this->arguments);

        if ($this->getOption('h')) {
            $this->displayHelp();

            return;
        }

        if (!$name) {
            $this->error('No name specifed to create');
        }

        switch ($action) {
            case 'migration':
                (new CreateMigration())->execute();
                break;
            case 'module':
                (new CreateModule())->execute();
                break;

            case 'command':
                (new CreateCommand())->execute();
                break;

            default:
                $this->showError('Unknow create command: ' . $action);
        }

        return;
    }

    protected function getTemplate(string $fileName, array $variables = []): string
    {
        $templatePath = $this->template_dir . $fileName;

        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template file does not exist: ' . $templatePath);
        }

        $templateContent = file_get_contents($templatePath);

        foreach ($variables as $key => $value) {
            $templateContent = str_replace('{{' . $key . '}}', $value, $templateContent);
        }

        return $templateContent;
    }

    public static function isValidSubCommand(string $name)
    {
        $subCommands = [
            'migration',
            'module',
            'command',
        ];
        $command = explode(':', $name);
        array_shift($command);
        if (count($command) > 1) {
            return false;
        }
        $command = reset($command);

        return in_array($command, $subCommands);
    }
}
