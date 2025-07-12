<?php

namespace System\Core\Command\Create;

class CreateMigration extends Create
{
    protected static string $name = 'create-migration';
    protected string $description = 'Create a new migration file';

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

        $this->showMessage('Creating Migration: ' . $name);
        $this->createMigration($name);
        $this->showSuccess('Migration created successfully');
    }
    private function createMigration(string $name): void
    {
        $migrationsDir = $this->getConfig()->get('migration', [])['path'] ?? null;

        if (!$migrationsDir) {
            $this->error('Migration path is not configured');

            return;
        }
        $filePath = $migrationsDir . '/' . $name;

        // Get filename (without extension) for class name
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);

        // Optional: sanitize class name using timestamp + path segments
        $timestamp = date('Ymd_His');
        $classBase = ucfirst($fileName);
        $className = 'Migration_' . $timestamp . '_' . $classBase;
        $filePath = str_replace($fileName, $className, $filePath);
        $content = $this->getTemplate('migration.stub',
            [
            'className' => $className,
            ]);
        // Ensure directory exists
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Append .php if not present
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
            $filePath .= '.php';
        }

        if (file_exists($filePath)) {
            $this->error("Migration file already exists: $filePath");

            return;
        }

        // Write the file
        if (file_put_contents($filePath, $content) === false) {
            $this->error("Failed to write migration file: $filePath");
        }

        $this->showSuccess("Migration file created: $filePath");
    }
}
