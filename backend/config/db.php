<?php
/**
 * Adatbázis kapcsolat osztály
 *
 * Ez az osztály felelős a MySQL adatbázishoz való kapcsolódásért PDO-n keresztül.
 * A kapcsolati adatokat környezeti változókból olvassa be.
 */

include_once 'env_loader.php';

// CORS (Cross-Origin Resource Sharing) headereк beállítása
// Ez lehetővé teszi, hogy a frontend (más origin) hozzáférjen az API-hoz
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// OPTIONS kérések kezelése (preflight requests)
// A böngésző először ezt küldi el CORS kérés esetén
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Database osztály - MySQL adatbázis kapcsolat kezelése
 *
 * Az osztály PDO-t használ a biztonságos adatbázis eléréséhez.
 * A kapcsolati adatokat a .env fájlból olvassa be.
 */
class Database
{
    /** @var string Adatbázis szerver címe */
    private $host;

    /** @var string Adatbázis neve */
    private $db_name;

    /** @var string Adatbázis felhasználónév */
    private $username;

    /** @var string Adatbázis jelszó */
    private $password;

    /** @var PDO|null PDO kapcsolat objektum */
    public $conn;

    /**
     * Konstruktor - Betölti a kapcsolati adatokat környezeti változókból
     *
     * Ha egy környezeti változó nincs beállítva, alapértelmezett értéket használ.
     */
    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'react_php_auth';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: 'root';
    }

    /**
     * Adatbázis kapcsolat létrehozása
     *
     * PDO objektumot hoz létre a beállított kapcsolati adatokkal.
     * UTF-8 karakterkódolást állít be az adatbázis kommunikációhoz.
     *
     * @return PDO|null PDO kapcsolat objektum vagy null hiba esetén
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            // PDO kapcsolat létrehozása
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);

            // UTF-8 karakterkódolás beállítása
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            // Kapcsolati hiba esetén hibaüzenet kiírása
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>