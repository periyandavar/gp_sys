<?php

namespace System\Core\Command;

use System\Core\Command\Create\Create;
use System\Core\Console;

class Runner extends Console
{
    protected static string $name = 'run';
    protected string $description = 'Run a system command';

    /**
     * Define short and long options for getopt().
     */
    protected function options(): array
    {
        $options = parent::options();
        $options = array_merge($options, [
            'cmd:' => [
                'short' => 'c:',
                'message' => 'The command to run'
            ]
        ]);

        return $options;
    }

    /**
     * Entry point for the command execution.
     */
    public function run(): void
    {
        $command = $this->getOption('c');
        $is_help_cmd = $this->getOption('h');

        if (!$command) {
            $this->showWarning("No command specified. Use -c or --cmd to specify a command.\n");

            return;
        }
        $this->showInfo("Running command: {$command}\n");
        switch ($command) {
            case 'migrate':
                $this->runMigration();

                return;
            case 'rollback':
                $this->runRollback();
                break;
            case 'create':
                (new Create())->execute();

                return;
            case 'welcome':
                (new Welcome())->execute();

                return;
            default:
                $this->showError("Unknown command: {$command}\n", );
        }

        if ($is_help_cmd) {
            $this->handleHelp();

            return;
        }
    }

    public function runMigration(): void
    {
        $migrator = new Migrator();
        $migrator->execute();
    }
    public function runRollback(): void
    {
        $migrator = new Migrator();
        $migrator->execute();
    }
}
