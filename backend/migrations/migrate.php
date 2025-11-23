<?php
/**
 * Adatbázis Migráció Script
 *
 * Ez a script automatikusan létrehozza az adatbázist és a szükséges táblákat.
 * A kapcsolati adatokat a .env fájlból olvassa be.
 *
 * Futtatás: php backend/migrations/migrate.php
 *
 * Lépések:
 * 1. Kapcsolódás a MySQL szerverhez
 * 2. Adatbázis létrehozása (ha nem létezik)
 * 3. SQL migrációs fájl végrehajtása (táblák és adatok létrehozása)
 */

require_once __DIR__ . '/../config/env_loader.php';

/**
 * Migrator osztály - Adatbázis migrációk kezelése
 */
class Migrator
{
    /** @var string MySQL szerver címe */
    private $host;

    /** @var string Adatbázis neve */
    private $db_name;

    /** @var string Adatbázis felhasználónév */
    private $username;

    /** @var string Adatbázis jelszó */
    private $password;

    /** @var PDO|null PDO kapcsolat objektum */
    private $conn;

    /**
     * Konstruktor - Betölti a kapcsolati adatokat környezeti változókból
     */
    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'react_php_auth';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: 'root';
    }

    /**
     * Kapcsolódás a MySQL szerverhez (adatbázis kiválasztása nélkül)
     *
     * Ez szükséges ahhoz, hogy létre tudjuk hozni az adatbázist, ha még nem létezik.
     */
    public function connectServer()
    {
        try {
            // PDO kapcsolat létrehozása adatbázis kiválasztása nélkül
            $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected to MySQL server successfully.\n";
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage() . "\n");
        }
    }

    /**
     * Adatbázis létrehozása (ha még nem létezik)
     *
     * UTF8MB4 karakterkódolással hozza létre az adatbázist, hogy támogassa
     * az emojikat és a speciális Unicode karaktereket.
     */
    public function createDatabase()
    {
        try {
            // Adatbázis létrehozása UTF8MB4 kódolással
            $sql = "CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $this->conn->exec($sql);
            echo "Database '" . $this->db_name . "' created or already exists.\n";

            // Adatbázis kiválasztása a további műveleteкhez
            $this->conn->exec("USE `" . $this->db_name . "`");
        } catch (PDOException $e) {
            die("Error creating database: " . $e->getMessage() . "\n");
        }
    }

    /**
     * SQL migrációs fájl végrehajtása
     *
     * Beolvassa és végrehajtja a migrations.sql fájlt, amely tartalmazza
     * a táblák és kezdeti adatok létrehozásához szükséges SQL utasításokat.
     */
    public function runMigrations()
    {
        // Migrációs fájl helye
        $sqlFile = __DIR__ . '/migrations.sql';
        if (!file_exists($sqlFile)) {
            die("Migration file not found: $sqlFile\n");
        }

        // SQL fájl beolvasása
        $sql = file_get_contents($sqlFile);

        try {
            // SQL utasítások végrehajtása
            // A MySQL PDO általában támogatja a több utasításból álló scripteket
            $this->conn->exec($sql);
            echo "Tables and data migrated successfully.\n";
        } catch (PDOException $e) {
            die("Migration failed: " . $e->getMessage() . "\n");
        }
    }
}

// ========== Script végrehajtása ==========
$migrator = new Migrator();
$migrator->connectServer();    // 1. Kapcsolódás a MySQL szerverhez
$migrator->createDatabase();   // 2. Adatbázis létrehozása
$migrator->runMigrations();    // 3. Migrációk futtatása
?>