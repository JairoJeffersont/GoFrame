<?php

namespace GoFrame\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;


/**
 * Database Singleton Class
 * 
 * This class implements the Singleton pattern to ensure a single database connection
 * is maintained throughout the application lifecycle. It provides a centralized
 * point for database connection management using PDO for MySQL.
 *
 * Features:
 * - Singleton pattern implementation
 * - PDO connection with MySQL
 * - Secure configuration loading from .env file
 * - Prevention of cloning and unserialization
 * - Automatic error handling with PDOException
 *
 */
class Database {
    /**
     * Holds the single instance of the Database class
     * 
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * Stores the PDO connection instance
     * 
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Private constructor to prevent direct instantiation
     * 
     * Loads configuration from .env and establishes database connection using PDO.
     * Sets up error handling and connection attributes.
     * 
     * @throws PDOException If connection fails or configuration is invalid
     */
    private function __construct() {

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $_ENV['DB_HOST'],
                $_ENV['DB_DATABASE'],
                $_ENV['DB_CHARSET']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO(
                $dsn,
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD'],
                $options
            );
        } catch (PDOException $e) {
            $error_id = Uuid::uuid4();
            $logger = new \GoFrame\Core\Helpers\Logger();
            $logger->newLog('connection.log', $error_id . ' | ' . $e->getMessage(), 'ERROR');

            $output = new \GoFrame\Core\Helpers\Output();

            $output->buildOutput([
                'status' => 'server_error',
                'status_code' => '500',
                'error_id' => $error_id
            ]);

            //throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Gets the single instance of the Database class
     * 
     * If no instance exists, creates one. Otherwise returns the existing instance.
     * This ensures only one database connection is maintained.
     * 
     * @return self The single Database instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Gets the PDO connection instance
     * 
     * Provides access to the underlying PDO connection for executing
     * database operations.
     * 
     * @return PDO The PDO connection instance
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Prevents cloning of the Database instance
     * 
     * This private method ensures the Singleton pattern is not violated
     * through cloning.
     * 
     * @return void
     */
    private function __clone() {
    }

    /**
     * Prevents unserialization of the Database instance
     * 
     * This method ensures the Singleton pattern is not violated through
     * PHP's unserialization mechanism.
     * 
     * @throws \Exception When attempting to unserialize the instance
     * @return void
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
