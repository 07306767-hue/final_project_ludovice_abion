<?php
namespace App\Helpers;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Connection Class
 *
 * Design Pattern: Singleton Pattern
 * Ensures only one database connection instance exists throughout the application.
 * This prevents multiple connections and improves performance.
 *
 * Try-catch Usage: All database operations are wrapped in try-catch blocks
 * to handle PDOExceptions gracefully without crashing the application.
 */
class Database {
    private static ?Database $instance = null; // Singleton instance
    private ?PDO $pdo = null;

    private $connection; // Actual PDO connection
    private $config; // Database configuration array

    // Private constructor prevents direct instantiation (Singleton pattern)
    private function __construct() {
        $this->loadConfig();
        $this->connect();
    }

    private function loadConfig()
    {
        // Design Pattern: Configuration Management
        // Sensitive data → ENV: Database credentials loaded from environment variables
        // If sensitive → ENV: Passwords, API keys, and connection details should never be hardcoded
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'name' => getenv('DB_NAME') ?: 'lms_db',
            'user' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '', // Sensitive → ENV
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'driver' => getenv('DB_DRIVER') ?: 'mysql'
        ];

        if (!$this->config['name'] || !$this->config['user']) {
            throw new \Exception("Database name and user are required in .env file");
        }
    }

    private function connect()
    {
        try {
            // Build DSN (Data Source Name) for PDO connection
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['name'],
                $this->config['charset']
            );

            // Create PDO connection with error handling
            $this->connection = new PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays
                    PDO::ATTR_EMULATE_PREPARES => false // Use real prepared statements
                ]
            );

        } catch (PDOException $e) {
            // Try-catch: Handle connection failures gracefully
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    // Prevent cloning (Singleton pattern)
    private function __clone() {}

    // Prevent unserialization (Singleton pattern)
    public function __wakeup() {
        throw new RuntimeException("Cannot unserialize singleton");
    }

    /**
     * Get singleton instance
     * Design Pattern: Singleton Pattern - Global access point
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql): array {
        try {
            if ($this->connection === null) {
                return [];
            }

            $stmt = $this->connection->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return [];
        }
    }
    }

?>
