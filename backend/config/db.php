<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Database
{
    private $host = "localhost";
    private $db_name = "react_php_auth";
    private $username = "root";
    private $password = "root";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            // Fallback for testing without creating DB manually if possible, or just error out
            // For this demo, we assume the DB exists.
            // If connection fails, we might try to connect without dbname to create it?
            // Let's keep it simple.
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>