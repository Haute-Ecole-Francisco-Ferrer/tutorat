<?php
/**
 * Database connection singleton class
 * Handles database connection using PDO with proper configuration and error handling
 */
class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    
    // Database configuration
    private const LOCAL_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'tutorat', 
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8mb4'
    ];
    
    private const REMOTE_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'uhti7837_tutorat', 
        'username' => 'uhti7837_tutorat',
        'password' => 'O;i92](_Tu*8',
        'charset' => 'utf8mb4'
    ];

    // PDO options
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    private function __construct() {
        $this->connect();
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    private function connect(): void {
        try {
            // Determine if we're on local or remote environment
            $isLocal = $this->isLocalEnvironment();
            $config = $isLocal ? self::LOCAL_CONFIG : self::REMOTE_CONFIG;
            
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['dbname'],
                $config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                self::PDO_OPTIONS
            );
            
            // Add debug log
            error_log("Database connection successful (Environment: " . ($isLocal ? "Local" : "Remote") . ")");
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new PDOException("Unable to connect to database. Please try again later.");
        }
    }

    /**
     * Determine if we're running in a local environment
     * @return bool True if local, false if remote
     */
    private function isLocalEnvironment(): bool {
        // Method 0: Check for environment variable (highest priority)
        if (getenv('DB_ENV') !== false) {
            return getenv('DB_ENV') === 'local';
        }
        
        // Method 1: Check server hostname
        $hostname = gethostname();
        if (strpos($hostname, 'localhost') !== false || 
            strpos($hostname, 'DESKTOP') !== false || 
            strpos($hostname, 'MacBook') !== false) {
            return true;
        }
        
        // Method 2: Check server IP
        $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
        if ($serverAddr === '127.0.0.1' || $serverAddr === '::1') {
            return true;
        }
        
        // Method 3: Check if running on localhost domain
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        if ($serverName === 'localhost' || 
            strpos($serverName, '.local') !== false || 
            strpos($serverName, '.test') !== false ||
            strpos($serverName, 'MAMP') !== false) {
            return true;
        }
        
        // Method 4: Check for common local file paths
        if (file_exists('/Applications/MAMP') || file_exists('C:\\MAMP')) {
            return true;
        }
        
        // Default to remote if we can't determine
        return false;
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
