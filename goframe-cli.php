#!/usr/bin/env php
<?php

function prompt(string $message, $default = ''): string {
    echo "$message" . ($default ? " [$default]" : '') . ": ";
    $input = trim(fgets(STDIN));
    return $input === '' && $default !== '' ? $default : $input;
}

function promptYesNo(string $message, bool $default = false): bool {
    $defaultStr = $default ? 'Y/n' : 'y/N';
    $input = strtolower(prompt("$message ($defaultStr)"));
    if ($input === '') return $default;
    return in_array($input, ['y', 'yes']);
}

function exportArrayPretty(array $array, int $level = 2): string {
    $indent = str_repeat(' ', $level * 2);
    $code = "[\n";
    foreach ($array as $key => $value) {
        $code .= $indent . "'$key' => ";
        if (is_array($value)) {
            $code .= exportArrayPretty($value, $level + 1);
        } else {
            $code .= var_export($value, true);
        }
        $code .= ",\n";
    }
    $code .= str_repeat(' ', ($level - 1) * 2) . "]";
    return $code;
}

$modelName = prompt("Model name (e.g.: User)");
$tableName = prompt("Table name (e.g.: users)", strtolower($modelName) . 's');
$sync = promptYesNo("Synchronize table structure?", false);

$columns = [];
do {
    $colName = prompt("Column name (or press ENTER to finish)");
    if (!$colName) break;

    $type = prompt("Type (e.g.: varchar(36), int, text)");
    $required = promptYesNo("Required?", true);
    $primary = promptYesNo("Primary key?", false);
    $autoIncrement = promptYesNo("Auto increment?", false);
    $unique = promptYesNo("Unique?", false);

    $column = [
        'type' => $type,
        'required' => $required,
    ];
    if ($primary) $column['primary'] = true;
    if ($autoIncrement) $column['auto_increment'] = true;
    if ($unique) $column['unique'] = true;

    $columns[$colName] = $column;
} while (true);

// Generate model code
$columnsExport = exportArrayPretty($columns, 2);
$syncValue = $sync ? 'true' : 'false';

$modelCode = <<<PHP
<?php

namespace GoFrame\Models;

use GoFrame\Core\Mysql\BaseModel;

/**
 * Class $modelName
 * 
 * Model class for the "$tableName" table generated via CLI.
 * 
 * @package GoFrame\Models
 */
class $modelName extends BaseModel {
    protected string \$table = '$tableName';
    protected bool \$sync = $syncValue;

    protected array \$columns = $columnsExport;

    public function __construct() {
        parent::__construct();
        if (\$this->sync) {
            \$this->syncTable(\$this->table, \$this->columns);
        }
    }

    public function getColumns(): array {
        return \$this->columns;
    }
}
PHP;

// Save file
$filename = __DIR__ . "/src/Models/$modelName.php";
file_put_contents($filename, $modelCode);
echo "✅ Model successfully generated: $filename\n";
