<?php

namespace GoFrame\Core\Database;

use GoFrame\Config\Database;
use PDO;

/**
 * Abstract class BaseModel
 * 
 * Base class for models providing basic CRUD and schema synchronization functionalities.
 * Uses PDO for database interaction.
 * 
 * @package GoFrame\Models
 */
abstract class BaseModel {
    /**
     * @var string Name of the database table associated with the model.
     */
    protected string $table;

    /**
     * @var PDO PDO instance for database connection.
     */
    protected PDO $db;

    /**
     * Constructor.
     * Initializes the PDO connection from the Database singleton.
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Synchronizes the database table structure with the provided column definitions.
     * Adds, modifies, or removes columns, primary keys, and unique indexes as necessary.
     * 
     * Example of $columns parameter:
     * ```php
     * $columns = [
     *     'id' => ['type' => 'INT(11)', 'primary' => true, 'auto_increment' => true],
     *     'name' => ['type' => 'VARCHAR(255)', 'required' => true, 'unique' => true],
     *     'email' => ['type' => 'VARCHAR(255)', 'required' => false, 'default' => null],
     * ];
     * ```
     * 
     * @param string $tableName Name of the table to synchronize.
     * @param array $columns Associative array of column definitions.
     * @return void
     * 
     * @throws \PDOException on SQL errors.
     */
    function syncTable(string $tableName, array $columns): void {
        // Verifica se a tabela existe
        $tableNameEscaped = str_replace('`', '``', $tableName); // proteção básica contra injeção
        $stmt = $this->db->query("SHOW TABLES LIKE " . $this->db->quote($tableNameEscaped));
        $tableExists = $stmt->fetchColumn() !== false;


        if (!$tableExists) {
            // Constrói a definição das colunas
            $colDefs = [];
            $primaryKey = null;
            $uniqueKeys = [];

            foreach ($columns as $name => $props) {
                $colDef = "`$name` {$props['type']}";
                $colDef .= !empty($props['required']) ? ' NOT NULL' : ' NULL';

                if (!empty($props['auto_increment'])) {
                    $colDef .= ' AUTO_INCREMENT';
                }

                if (array_key_exists('default', $props) && empty($props['primary'])) {
                    $default = $props['default'];
                    if (is_null($default)) {
                        $colDef .= ' DEFAULT NULL';
                    } elseif (is_string($default)) {
                        $colDef .= " DEFAULT " . $this->db->quote($default);
                    } else {
                        $colDef .= " DEFAULT $default";
                    }
                }

                $colDefs[] = $colDef;

                if (!empty($props['primary'])) {
                    $primaryKey = $name;
                }

                if (!empty($props['unique'])) {
                    $uniqueKeys[] = $name;
                }
            }

            if ($primaryKey) {
                $colDefs[] = "PRIMARY KEY (`$primaryKey`)";
            }

            foreach ($uniqueKeys as $unique) {
                $indexName = "uniq_{$tableName}_{$unique}";
                $colDefs[] = "UNIQUE KEY `$indexName` (`$unique`)";
            }

            $sql = "CREATE TABLE `$tableName` (" . implode(", ", $colDefs) . ")";
            $this->db->exec($sql);
            return; 
        }

        // A tabela existe, segue com a sincronização normal
        $stmt = $this->db->query("SHOW COLUMNS FROM `$tableName`");
        $existingColumns = [];
        $primaryColumns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[$row['Field']] = $row;
            if ($row['Key'] === 'PRI') {
                $primaryColumns[] = $row['Field'];
            }
        }

        $stmt = $this->db->query("SHOW INDEX FROM `$tableName` WHERE Non_unique = 0 AND Key_name != 'PRIMARY'");
        $uniqueIndexes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $uniqueIndexes[$row['Column_name']] = $row['Key_name'];
        }

        foreach ($columns as $name => $props) {
            $colDef = "`$name` {$props['type']}";
            $colDef .= !empty($props['required']) ? ' NOT NULL' : ' NULL';
            if (!empty($props['auto_increment'])) {
                $colDef .= ' AUTO_INCREMENT';
            }
            if (array_key_exists('default', $props) && empty($props['primary'])) {
                $default = $props['default'];
                if (is_null($default)) {
                    $colDef .= ' DEFAULT NULL';
                } elseif (is_string($default)) {
                    $colDef .= " DEFAULT " . $this->db->quote($default);
                } else {
                    $colDef .= " DEFAULT $default";
                }
            }

            if (isset($existingColumns[$name])) {
                $this->db->exec("ALTER TABLE `$tableName` MODIFY COLUMN $colDef");
            } else {
                $this->db->exec("ALTER TABLE `$tableName` ADD COLUMN $colDef");
            }
        }

        foreach ($existingColumns as $existingName => $col) {
            if (!array_key_exists($existingName, $columns)) {
                $this->db->exec("ALTER TABLE `$tableName` DROP COLUMN `$existingName`");
            }
        }

        $primaryInColumns = null;
        foreach ($columns as $name => $props) {
            if (!empty($props['primary'])) {
                $primaryInColumns = $name;
                break;
            }
        }

        if ($primaryInColumns === null && !empty($primaryColumns)) {
            $this->db->exec("ALTER TABLE `$tableName` DROP PRIMARY KEY");
        } elseif ($primaryInColumns !== null) {
            if (count($primaryColumns) !== 1 || $primaryColumns[0] !== $primaryInColumns) {
                if (!empty($primaryColumns)) {
                    $this->db->exec("ALTER TABLE `$tableName` DROP PRIMARY KEY");
                }
                $this->db->exec("ALTER TABLE `$tableName` ADD PRIMARY KEY (`$primaryInColumns`)");
            }
        }

        foreach ($columns as $name => $props) {
            $hasUnique = !empty($props['unique']);
            $hasUniqueIndex = isset($uniqueIndexes[$name]);

            if ($hasUnique && !$hasUniqueIndex) {
                $indexName = "uniq_{$tableName}_{$name}";
                $this->db->exec("ALTER TABLE `$tableName` ADD UNIQUE `$indexName` (`$name`)");
            } elseif (!$hasUnique && $hasUniqueIndex) {
                $indexName = $uniqueIndexes[$name];
                $this->db->exec("ALTER TABLE `$tableName` DROP INDEX `$indexName`");
            }
        }
    }


    /**
     * Fetches paginated records from the table, optionally filtered by a column, and ordered.
     *
     * Example:
     * ```php
     * $result = $model->findAll('created_at', 'DESC', 1, 10);
     * 
     * $result = $model->findAll('created_at', 'DESC', 1, 10, 'status', 'active');
     * ```
     *
     * @param string|null $column Column name to filter by (optional).
     * @param mixed|null $value Value to filter the column by (optional).
     * @param string|null $orderBy Column name to order by, or null for no ordering.
     * @param string $order Sort direction: 'ASC' (default) or 'DESC'.
     * @param int $page Page number starting from 1 (default 1).
     * @param int $pageSize Number of records per page (default 10).
     * @return array Array with keys:
     *               - 'data' => array of rows for the current page
     *               - 'total' => int total number of rows in the filtered set
     *               - 'pages' => int total number of pages available
     *
     * @throws \PDOException on query failure.
     */
    public function findAll(
        ?string $column = null,
        mixed $value = null,
        ?string $orderBy = null,
        string $order = 'ASC',
        int $page = 1,
        int $pageSize = 10,

    ): array {
        $page = max(1, $page);
        $pageSize = max(1, $pageSize);
        $offset = ($page - 1) * $pageSize;

        $whereClause = '';
        $params = [];

        if ($column !== null && $value !== null) {
            $whereClause = " WHERE {$column} = :filterValue ";
            $params['filterValue'] = $value;
        }


        $stmtTotal = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} $whereClause");
        $stmtTotal->execute($params);
        $total = (int)$stmtTotal->fetchColumn();

        $pages = (int)ceil($total / $pageSize);


        $query = "SELECT * FROM {$this->table} $whereClause";
        if ($orderBy !== null) {
            $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
            $query .= " ORDER BY {$orderBy} {$order}";
        }
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', $pageSize, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'pages' => $pages,
            'data' => $data
        ];
    }



    /**
     * Finds a single record by a specific column and value.
     * 
     * Example:
     * ```php
     * $user = $userModel->findByColumn('email', 'example@example.com');
     * if ($user) {
     *     echo $user['name'];
     * }
     * ```
     * 
     * @param string $column Column name to search.
     * @param mixed $value Value to match.
     * @return array|null Associative array of the found row or null if not found.
     * 
     * @throws \PDOException on query failure.
     */
    public function findOne(string $column, mixed $value): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $stmt->execute(['value' => $value]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Inserts a new record into the table.
     * 
     * Example:
     * ```php
     * $newId = $userModel->insert([
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com'
     * ]);
     * echo "Inserted record ID: " . $newId;
     * ```
     * 
     * @param array $data Associative array of column => value pairs.
     * @return int The last inserted ID.
     * 
     * @throws \PDOException on insert failure.
     */
    public function insert(array $data): int {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_map(fn($key) => ':' . $key, array_keys($data)));
        $stmt = $this->db->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Updates records matching the given column and value with new data.
     * 
     * Example:
     * ```php
     * $success = $userModel->update('id', 1, [
     *     'name' => 'Jane Doe',
     *     'email' => 'jane@example.com'
     * ]);
     * if ($success) {
     *     echo "Record updated successfully.";
     * }
     * ```
     * 
     * @param string $column Column name to identify records.
     * @param mixed $value Value to match in the column.
     * @param array $data Associative array of columns to update with new values.
     * @return bool True on success, false on failure.
     * 
     * @throws \PDOException on update failure.
     */
    public function update(string $column, mixed $value, array $data): bool {
        $setClause = implode(',', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data['__where_value'] = $value;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET $setClause WHERE {$column} = :__where_value");
        return $stmt->execute($data);
    }

    /**
     * Deletes records matching the given column and value.
     * 
     * Example:
     * ```php
     * $deleted = $userModel->delete('id', 1);
     * if ($deleted) {
     *     echo "Record deleted successfully.";
     * }
     * ```
     * 
     * @param string $column Column name to identify records.
     * @param mixed $value Value to match in the column.
     * @return bool True on success, false on failure.
     * 
     * @throws \PDOException on delete failure.
     */
    public function delete(string $column, mixed $value): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$column} = :value");
        return $stmt->execute(['value' => $value]);
    }
}
