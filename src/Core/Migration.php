<?php

namespace System\Core;

use Database\Database;
use Loader\Config\ConfigLoader;
use System\Core\Exception\FrameworkException;

/**
 * Abstract base class for database migrations.
 *
 * Child classes should implement up() and down() methods.
 */
abstract class Migration
{
    /**
     * @var ?Database
     */
    protected ?Database $db;
    protected string $dbName = 'default';
    protected string $dbEngine = 'InnoDB';

    /**
     * Directory where migration files reside.
     * @var string
     */
    public static string $migrationsDir = __DIR__ . '/migrations/';
    protected static string $prefix = '';

    /**
     * set the config values.
     *
     * @param  \Loader\Config\ConfigLoader $config
     * @return void
     */
    public function setConfig(ConfigLoader $config): void
    {
        $this->dbName = $config->get('db_name', 'default');
        $this->dbEngine = $config->get('db_engine', 'InnoDB');
        static::$migrationsDir = $config->get('migrations_dir', __DIR__ . '/migrations/');
        static::$prefix = $config->get('db_prefix', '');
    }

    /**
     * Migration constructor.
     *
     * @throws FrameworkException
     */
    public function __construct()
    {
        $this->db = Utility::getDb($this->dbName);
        if (!$this->db) {
            throw new FrameworkException('Database connection is not established. Please check your database configuration.', FrameworkException::DB_CONNECTION_ERROR);
        }
        $this->ensureSchemaTable();
    }

    /**
     * Apply schema changes.
     */
    abstract public function up(): void;

    /**
     * Revert schema changes.
     */
    abstract public function down(): void;

    /**
     * Ensures the migrations tracking table exists.
     */
    protected function ensureSchemaTable(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE={$this->dbEngine};"
        );
    }

    /**
     * Checks which migrations have been applied.
     *
     * @return array
     */
    protected function getAppliedMigrations(): array
    {
        $this->db->query('SELECT migration FROM migrations ORDER BY id ASC');

        return $this->db->getAll();
    }

    /**
     * Records a migration as applied.
     */
    protected function recordMigration(string $name): void
    {
        $this->db->query('INSERT IGNORE INTO migrations (migration) VALUES (?)', ['migration' => $name]);
    }

    /**
     * Removes a migration record (for rollbacks).
     */
    protected function removeMigrationRecord(string $name): void
    {
        $this->db->query('DELETE FROM migrations WHERE migration = ?', [$name]);
    }

    /**
     * Runs all pending migrations in the given directory.
     */
    public function migrate(): void
    {
        $applied = $this->getAppliedMigrations();
        $applied = array_map(fn ($item) => $item->migration, $applied);

        $name = basename(static::class);

        if (!in_array($name, $applied)) {
            $this->up();
            $this->recordMigration($name);
            echo "Applied: $name\n";

            return;
        }
        echo "Migration $name has already been applied.\n";
    }

    /**
     * Rolls back the last applied migration.
     */
    public function rollback(): void
    {
        $applied = $this->getAppliedMigrations();
        if (empty($applied)) {
            echo "No migrations to rollback.\n";

            return;
        }
        $name = basename(static::class);
        $applied = array_map(fn ($item) => $item->migration, $applied);
        if (in_array($name, $applied)) {
            $this->down();
        }

        $this->removeMigrationRecord($name);
        echo "Rolled back: $name\n";
    }

    /**
     * Create table.
     *
     * @param  string                    $tableName
     * @param  array                     $columns
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function createTable(string $tableName, array $columns): void
    {
        if (empty($columns)) {
            throw new \InvalidArgumentException('Columns array cannot be empty.');
        }

        $columnDefinitions = [];

        foreach ($columns as $name => $definition) {
            // Add backticks around column names to prevent SQL errors with reserved keywords
            $columnDefinitions[] = "`$name` $definition";
        }

        $columnsSql = implode(', ', $columnDefinitions);
        $sql = "CREATE TABLE IF NOT EXISTS `$tableName` ($columnsSql) ENGINE=InnoDB;";

        $this->executeQuery($sql);
    }

    /**
     * Insert the data
     *
     * @param  string                                    $tableName
     * @param  array                                     $data
     * @throws \System\Core\Exception\FrameworkException
     * @return void
     */
    public function insert(string $tableName, array $data): void
    {
        if (empty($data)) {
            throw new FrameworkException('No data provided for insertion.', FrameworkException::INVALID_ARGUMENT);
        }

        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `$tableName` (`$columns`) VALUES ($placeholders)";

        $this->executeQuery($sql, array_values($data));
    }

    /**
     * Update the data.
     *
     * @param  string                                    $tableName
     * @param  array                                     $data
     * @param  string                                    $where
     * @throws \System\Core\Exception\FrameworkException
     * @return void
     */
    public function updateTable(string $tableName, array $data, string $where): void
    {
        if (empty($data)) {
            throw new FrameworkException('No data provided for update.', FrameworkException::INVALID_ARGUMENT);
        }

        $setClause = implode(', ', array_map(fn ($col) => "`$col` = ?", array_keys($data)));
        $sql = "UPDATE `$tableName` SET $setClause WHERE $where";

        $this->executeQuery($sql, array_values($data));
    }

    /**
     * Delete table entry.
     *
     * @param  string                                    $tableName
     * @param  string                                    $where
     * @throws \System\Core\Exception\FrameworkException
     * @return void
     */
    public function deleteTable(string $tableName, string $where): void
    {
        if (empty($where)) {
            throw new FrameworkException('No condition provided for deletion.', FrameworkException::INVALID_ARGUMENT);
        }

        $sql = "DELETE FROM `$tableName` WHERE $where";
        $this->executeQuery($sql);
    }

    /**
     * Alter the table.
     *
     * @param string $tableName
     * @param string $alteration
     *
     * @return void
     */
    public function alterTable(string $tableName, string $alteration): void
    {
        $sql = "ALTER TABLE `$tableName` $alteration";
        $this->executeQuery($sql);
    }

    /**
     * Drops an index from a table.
     *
     * @param string $tableName The name of the table.
     * @param string $indexName The name of the index to drop.
     */
    public function dropIndex(string $tableName, string $indexName): void
    {
        $sql = "DROP INDEX `$indexName` ON `$tableName`;";
        $this->executeQuery($sql);
    }

    /**
     * Drop table.
     *
     * @param  string $tableName
     * @return void
     */
    public function dropTable(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS `$tableName`;";
        $this->executeQuery($sql);
    }

    /**
     * Creates an index on a table.
     *
     * @param string $tableName The name of the table.
     * @param string $indexName The name of the index.
     * @param array  $columns   An array of column names to include in the index.
     * @param bool   $unique    Whether the index should be unique.
     */
    public function createIndex(string $tableName, string $indexName, array $columns, bool $unique = false): void
    {
        $columnsList = implode(', ', array_map(fn ($col) => "`$col`", $columns));
        $uniqueClause = $unique ? 'UNIQUE' : '';
        $sql = "CREATE $uniqueClause INDEX `$indexName` ON `$tableName` ($columnsList);";
        $this->executeQuery($sql);
    }

    /**
     * Return Migration name.
     *
     * @return string
     */
    public function getName()
    {
        return static::class;
    }

    /**
     * Executes query.
     *
     * @param string $sql
     * @param array  $params
     *
     * @return void
     */
    public function executeQuery(string $sql, array $params = []): void
    {
        $this->db->query($sql, $params);
    }
}
