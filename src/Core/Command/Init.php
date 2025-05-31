<?php

namespace System\Core\Command;

use System\Core\Console;
use System\Core\Constants;

class Init extends Console
{
    protected static string $name = 'init';
    protected string $description = 'Initialize the system framework';

    /**
     * Define short and long options for getopt().
     */
    protected function options(): array
    {
        $options = parent::options();
        $options = array_merge($options, [
            'env:' => [
                'short' => 'e:',
                'message' => 'The environment to initialize (e.g., development, production)',
                'default' => Constants::ENV_DEV
            ],
            'suppress_errors' => [
                'short' => 's',
                'message' => 'Suppress error messages in the application',
                'default' => false,
            ],
        ]);

        return $options;
    }

    /**
     * Entry point for the command execution.
     */
    public function run(): void
    {
        $env = $this->getOption('e');

        if (!in_array($env, Constants::VALID_ENV)) {
            $this->showError("Invalid environment specified: {$env}. Valid options are: " . implode(', ', Constants::VALID_ENV) . "\n");

            return;
        }

        $this->showInfo("Initializing the system for the environment: {$env}...\n");
        if ($this->getOption('h')) {
            $this->displayHelp();

            return;
        }

        $suppressErrors = $this->getOption('s');
        $suppressErrors = $suppressErrors ? 'true' : 'false';

        $this->createFile('index.web.stub', 'index.php', [
            'env' => $env,
            'suppress_errors' => $suppressErrors,
        ], 'web index file');

        $this->createFile('index.console.stub', 'console/index.php', [
            'env' => $env,
            'suppress_errors' => $suppressErrors,
        ], 'web index file');

        $this->createFile('run.stub', 'console/run', [
        ], 'run file');

        $this->showSuccess("System initialized successfully for environment: {$env}\n");
        $this->showInfo("Run php console/run migrate to initialize the database.\n");
        $this->showInfo("You can now run the console commands using: php console/run.php <command>\n");
        $this->showInfo("For web access, use: php index.php\n");
    }

    protected function createFile(string $fileName, string $outputFile, array $variables = [], $helpText = '')
    {
        $dir = __DIR__ . '/Create/templates/';
        if (!is_dir($dir)) {
            throw new \RuntimeException('Template directory does not exist: ' . $dir);
        }
        $templatePath = $dir . '/' . $fileName;

        if (!file_exists($templatePath)) {
            throw new \RuntimeException('Template file does not exist: ' . $templatePath);
        }

        $templateContent = file_get_contents($templatePath);

        foreach ($variables as $key => $value) {
            $templateContent = str_replace('{{' . $key . '}}', $value, $templateContent);
        }

        $currentDir = getcwd();
        $filename = $currentDir . '/' . $outputFile;
        $helpText = empty($helpText) ?: '{' . $helpText . '}';
        if (file_exists($filename)) {
            throw new \RuntimeException("File already exists: {$filename}");
        }
        $this->showMessage("Creating file: {$filename}");
        file_put_contents($filename, $templateContent);
        $this->showSuccess("File {$helpText} created successfully: {$filename}");
    }
}
