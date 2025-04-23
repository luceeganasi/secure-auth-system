<?php
class Database {
    // Database connection parameters
    private $host = "localhost";      // Database server hostname
    private $db_name = "auth_system"; // Database name
    private $username = "root";       // Database username
    private $password = "";           // Database password
    public $conn;                     // Database connection object

    // Method to establish database connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Create new PDO connection
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Set error mode to throw exceptions
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            // Display connection error message
            echo "Connection Error: " . $e->getMessage();
        }

        // Return the database connection
        return $this->conn;
    }
}
?> 