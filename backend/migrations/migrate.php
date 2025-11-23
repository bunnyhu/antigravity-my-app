<?php
require_once __DIR__ . '/../config/env_loader.php';

class Migrator
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'react_php_auth';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: 'root';
    }

    public function connectServer()
    {
        try {
            $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected to MySQL server successfully.\n";
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage() . "\n");
        }
    }

    public function createDatabase()
    {
        try {
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->conn->exec($sql);
            echo "Database '" . $this->db_name . "' created or already exists.\n";
            $this->conn->exec("USE `" . $this->db_name . "`");
        } catch (PDOException $e) {
            die("Error creating database: " . $e->getMessage() . "\n");
        }
    }

    public function runMigrations()
    {
        $sqlFile = __DIR__ . '/migrations.sql';
        if (!file_exists($sqlFile)) {
            die("Migration file not found: $sqlFile\n");
        }

        $sql = file_get_contents($sqlFile);

        try {
            // Split SQL by semicolon to execute multiple statements if needed,
            // but PDO::exec can sometimes handle multiple statements if configured.
            // However, for safety and better error reporting, splitting is often safer
            // if the driver doesn't support multi-query by default or if we want progress.
            // But simple split by ; can break if ; is inside strings.
            // Given the simple migrations.sql, we can try executing the whole block or split carefully.
            // Let's try executing the whole block first as MySQL PDO often supports it.

            $this->conn->exec($sql);
            echo "Tables and data migrated successfully.\n";
        } catch (PDOException $e) {
            die("Migration failed: " . $e->getMessage() . "\n");
        }
    }
}

$migrator = new Migrator();
$migrator->connectServer();
$migrator->createDatabase();
$migrator->runMigrations();
?>