<?php

namespace System\Core\Command;

use System\Core\Console;

class Welcome extends Console
{
    protected static string $name = 'welcome';
    protected string $description = 'Display a welcome message';

    /**
     * Define short and long options for getopt().
     */
    protected function options(): array
    {
        $options = parent::options();

        return $options;
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

        $this->showSuccess("Welcome to the System!\n");
    }
}
