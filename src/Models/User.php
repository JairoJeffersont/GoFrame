<?php

namespace GoFrame\Models;

use GoFrame\Core\Mysql\BaseModel;

/**
 * Class User
 * 
 * Model class for the "users" table.
 * Extends BaseModel and automatically syncs the table structure on instantiation.
 * 
 * @package GoFrame\Models
 */
class User extends BaseModel {
    /**
     * @var string Name of the table associated with this model.
     */
    protected string $table = 'users';

    /**
     * @var bool Whether to synchronize the table structure on object creation.
     */
    protected bool $sync = true; //WARNING!! WHEN SYNCHRONIZING THE TABLE DATA MODEL, DATA WILL BE LOST!

    /**
     * @var array Definition of columns and their properties for synchronization.
     * Example:
     * ```php
     * [
     *     'id' => [
     *         'type' => 'varchar(36)',
     *         'required' => true,
     *         'primary' => true,
     *         'unique' => false    
     *     ]
     * ]
     * ```
     */
    protected array $columns = [
        'id' => [
            'type' => 'varchar(36)',
            'required' => true,
            'primary' => true
        ],
        'name' => [
            'type' => 'varchar(36)',
            'required' => true
        ],
        'email' => [
            'type' => 'varchar(36)',
            'required' => true,
            'unique' => true
        ],
        'foto' => [
            'type' => 'varchar(100)'
        ]
    ];

    /**
     * Constructor.
     * Calls parent constructor and synchronizes table structure if $sync is true.
     */
    public function __construct() {
        parent::__construct();
        if ($this->sync) {
            $this->syncTable($this->table, $this->columns);
        }
    }

    /**
     * Returns the columns definition array.
     * 
     * @return array Array of columns and their properties.
     */
    public function getColumns(): array {
        return $this->columns;
    }
}
