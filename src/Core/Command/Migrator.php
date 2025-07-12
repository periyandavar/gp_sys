<?php

namespace System\Core\Command;

use System\Core\Console;
use System\Core\Migration;
use System\Core\Utility;

class Migrator extends Console
{
    protected static string $name = 'migrate';
    protected string $description = 'Run database migrations';

    /**
     * Define short and long options for getopt().
     */
    public function options(): array
    {
        $options = parent::options();
        $options = array_merge($options, [
            'rollback' => [
                'short' => 'r',
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
        // Display help message or perform the migration
        if ($this->getOption('h')) {
            $this->handleHelp();

            return;
        }

        if ($this->getOption('rollback')) {
            $this->showInfo("Running rollback...\n");
            try {
                $this->rollback();
                $this->showSuccess("Rollback completed successfully.\n");
            } catch (\Exception $e) {
                $this->showError('Error during rollback: ' . $e->getMessage() . "\n");
            }

            return;
        }

        $this->showInfo("Running migrations...\n");

        try {
            $this->migrate();
            $this->showSuccess("Migrations completed successfully.\n");
        } catch (\Exception $e) {
            $this->error('Error during migration: ' . $e->getMessage() . "\n", $e->getCode(), $e);
        }
    }

    private function getLastMigration()
    {
        $config = $this->getConfig();
        $db = Utility::getDb($config->get('db', 'default'));
        $db->select('migration')
            ->from('migrations')
            ->orderBy('applied_at', 'DESC')
            ->limit(1)
            ->execute();

        return $db->fetch();
    }

    private function rollback(): void
    {
        $config = $this->getConfig();

        $file = $this->options['migration'] ?? $this->getLastMigration()->migration ?? null;

        if (!$file) {
            throw new \RuntimeException('No migration file specified for rollback.');
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            $file .= '.php';
        }
        $migrationsDir = $migrationsDir = $this->getConfig()->get('migration', [])['path'] ?? null;
        $file = $migrationsDir . DS . $file;

        if (!file_exists($file)) {
            throw new \RuntimeException("Migration file does not exist: $file");
        }
        require_once $file;
        $ns = $config->get('namespace', '');
        $className = $ns . basename($file, '.php');

        if (class_exists($className)) {
            $migration = new $className();
            if ($migration instanceof \System\Core\Migration) {
                $this->showInfo('Found migration class: ' . $className . "\n");
                $this->showInfo('Rolling back migration: ' . $migration->getName() . "\n");
                Migration::$migrationsDir = $migrationsDir;
                $migration->rollback();
            } else {
                $this->showWarning('Skipping non-migration class: ' . $className . "\n");
            }
        } else {
            $this->showError('Class not found in file: ' . $file . "\n");
        }
    }
    private function migrate(): void
    {
        $config = $this->getConfig();

        $migrationsDir = $migrationsDir = $this->getConfig()->get('migration', [])['path'] ?? null;

        if (!is_dir($migrationsDir)) {
            throw new \RuntimeException("Migration path does not exist: $migrationsDir");
        }

        $migrationFiles = glob("$migrationsDir/*.php");

        if (empty($migrationFiles)) {
            $this->showWarning("No migration files found in $migrationsDir\n");

            return;
        }

        foreach ($migrationFiles as $file) {
            $this->showInfo('Processing migration file: ' . $file . "\n");
            require_once $file;
            $ns = $config->get('namespace', '');
            $className = $ns . basename($file, '.php');
            if (class_exists($className)) {
                $migration = new $className();
                if ($migration instanceof Migration) {
                    $this->showInfo('Found migration class: ' . $className . "\n");
                    $this->showInfo('Running migration: ' . $migration->getName() . "\n");
                    Migration::$migrationsDir = $migrationsDir;
                    $migration->migrate();
                } else {
                    $this->showWarning('Skipping non-migration class: ' . $className . "\n");
                }
            } else {
                $this->showError('Class not found in file: ' . $file . "\n");
            }
        }
    }
}
