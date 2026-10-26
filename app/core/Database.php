<?php
/**
 * MediMax Bootstrap - Database Connection Manager
 * 
 * Provides a PDO singleton connection to MySQL with automatic database
 * and table creation from medimax_schema.sql if needed.
 */
class Database {
    private static ?PDO $instance = null;
    private static ?string $connectionError = null;

    /**
     * Get the PDO database connection
     * 
     * @return PDO|null Returns PDO connection or null if connection failed
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Alias for getConnection()
     */
    public static function pdo(): ?PDO {
        return self::getConnection();
    }

    /**
     * Get last connection error message if any
     */
    public static function getError(): ?string {
        return self::$connectionError;
    }

    /**
     * Establish PDO connection and auto-initialize database schema if needed
     */
    private static function connect(): void {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $port = defined('DB_PORT') ? DB_PORT : '3306';
        $dbName = defined('DB_NAME') ? DB_NAME : 'medimax_db';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];

        try {
            // First try connecting directly to the configured database
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
            self::$instance = new PDO($dsn, $user, $pass, $options);
            self::$connectionError = null;

            // Ensure essential tables exist (e.g. users)
            self::ensureTablesExist(self::$instance, $dbName);
        } catch (PDOException $e) {
            // If the database does not exist, try connecting to MySQL server to create it
            if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database') || str_contains($e->getMessage(), 'database doesn\'t exist')) {
                try {
                    $serverDsn = "mysql:host={$host};port={$port};charset={$charset}";
                    $serverPdo = new PDO($serverDsn, $user, $pass, $options);
                    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
                    
                    // Now connect to the new database
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    self::$connectionError = null;
                    self::ensureTablesExist(self::$instance, $dbName);
                    return;
                } catch (PDOException $ex) {
                    self::$instance = null;
                    self::$connectionError = $ex->getMessage();
                    error_log("MediMax DB Creation Error: " . $ex->getMessage());
                    return;
                }
            }

            self::$instance = null;
            self::$connectionError = $e->getMessage();
            error_log("MediMax DB Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Ensure tables exist by running schema if the `users` table is missing
     */
    private static function ensureTablesExist(PDO $pdo, string $dbName): void {
        try {
            // Quick check if users table exists and is populated
            $check = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
            $needsSetup = false;

            if (!$check) {
                $needsSetup = true;
            } else {
                $count = (int)$pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
                if ($count === 0) {
                    $needsSetup = true;
                }
            }

            if ($needsSetup) {
                // Table does not exist or empty, execute medimax_schema.sql
                $rootDir = defined('APP_PATH') ? dirname(APP_PATH) : dirname(__DIR__, 2);
                $schemaFile = $rootDir . '/database/medimax_schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    if (!empty($sql)) {
                        $sqlToExecute = preg_replace('/CREATE DATABASE IF NOT EXISTS `[^`]+`[^;]*;/i', '', $sql);
                        $sqlToExecute = preg_replace('/USE `[^`]+`;/i', "USE `{$dbName}`;", $sqlToExecute);
                        $pdo->exec($sqlToExecute);

                        // Clear multi-statement buffer
                        while ($pdo->query("SELECT 1")->fetch()) { break; }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("MediMax Table Setup Error: " . $e->getMessage());
        }
    }
}
