<?php
class Dbh {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "election_system";
    private $conn = null;

    protected function connect() {
        // Reuse existing connection if available
        if ($this->conn !== null && !$this->conn->connect_error) {
            return $this->conn;
        }

        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset to prevent encoding issues
            $this->conn->set_charset("utf8mb4");
            
            return $this->conn;
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }

    // Close connection when object is destroyed
    public function __destruct() {
        if ($this->conn !== null) {
            $this->conn->close();
        }
    }
}
?>