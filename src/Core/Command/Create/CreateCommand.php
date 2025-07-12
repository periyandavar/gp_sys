<?php

namespace System\Core\Command\Create;

class CreateCommand extends Create
{
    protected static string $name = 'create-command';
    protected string $description = 'Create a new command';

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
            'description:' => [
                'short' => 'd:',
                'message' => 'command description'
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

        $this->showMessage('Creating Command: ' . $name);
        $this->createCommand($name);
        $this->showSuccess('Command created successfully');
    }

    private function createCommand(string $name): void
    {
        $commandsDir = defined('APP_DIR') ? APP_DIR : '';

        if (!is_dir($commandsDir)) {
            $this->error('Console directory does not exist: ' . $commandsDir);
        }

        $commandName = ucfirst($name);
        $filePath = $commandsDir . '/' . $commandName . '.php';

        if (file_exists($filePath)) {
            $this->error('Command already exists: ' . $name);
        }

        $content = $this->getTemplate('command.stub', [
            'name' => $name,
            'description' => $this->getOption('description') ?: "Description for {$name} command",
            'className' => $commandName,
        ]);
        // Create the command file with a basic template
        file_put_contents($filePath, $content);

        $app_dir = defined('APP_DIR') ? APP_DIR : '';
        $commandListFile = $app_dir . '/commands.php';

        $commands = [];

        if (file_exists($commandListFile)) {
            $commands = include $commandListFile;
            if (!is_array($commands)) {
                $this->error('Invalid command list file: ' . $commandListFile);
            }
        }
        $commands[$name] = $commandName;
        $content = $this->createCommandListContent($commands);

        file_put_contents($commandListFile, $content);

        $this->showMessage('Command file created at: ' . $filePath);
    }

    private function createCommandListContent(array $commands): string
    {
        $content = "<?php\n\nreturn [";

        foreach ($commands as $key => $command) {
            $content .= "\n\t'" . $key . "' => '" . $command . "',";
        }

        $content .= "\n];";

        return $content;
    }
}
