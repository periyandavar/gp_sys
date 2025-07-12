<?php

namespace System\Core\Command\Create;

class CreateModule extends Create
{
    protected static string $name = 'create-module';
    protected string $description = 'Create a new module';

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
        if ($this->getOption('h')) {
            $this->displayHelp();

            return;
        }

        $name = reset($this->arguments);

        if (!$name) {
            $this->error('No name specified to create');
        }

        $this->showMessage('Creating Module: ' . $name);
        $this->createModule($name);
        $this->showSuccess('Module created successfully');
    }

    public function createModule(string $name): void
    {
        $app_dir = defined('APP_DIR') ? APP_DIR : '';
        $modulePath = $app_dir . '/../src/Module';

        if (!is_dir($modulePath)) {
            throw new \RuntimeException('Module directory does not exist: ' . $modulePath);
        }
        $module = ucfirst($name);
        $modulePath .= '/' . $module;
        if (is_dir($modulePath)) {
            $this->error('Module already exists: ' . $name);
        }

        mkdir($modulePath, 0755, true);

        // Create basic directory structure
        mkdir($modulePath . '/Controller', 0755, true);
        mkdir($modulePath . '/Model', 0755, true);
        mkdir($modulePath . '/Service', 0755, true);

        // Create basic files for the module
        file_put_contents($modulePath . '/Module.php', $this->getTemplate('module.stub', ['name' => $name, 'module' => ucfirst($module)]));
        file_put_contents($modulePath . '/autoloads.php', $this->getTemplate('autoloads.stub'));
        file_put_contents($modulePath . '/services.php', $this->getTemplate('services.stub'));
        file_put_contents($modulePath . '/routes.php', $this->getTemplate('routes.stub'));

        $this->showSuccess("Module {$name} created successfully at {$modulePath}");
    }
}
