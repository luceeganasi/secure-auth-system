<?php
require_once 'config/database.php';
require_once 'vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

class Auth {
    private $conn;
    private $google2fa;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->google2fa = new Google2FA();
    }

    public function register($username, $email, $password, $role = 'user') {
        // Check if user already exists
        $query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return false;
        }

        // Generate 2FA secret
        $twoFactorSecret = $this->google2fa->generateSecretKey();

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $query = "INSERT INTO users (username, email, password, two_factor_secret, role) 
                 VALUES (:username, :email, :password, :two_factor_secret, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":two_factor_secret", $twoFactorSecret);
        $stmt->bindParam(":role", $role);

        return $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT id, password, two_factor_secret, role FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($password, $row['password'])) {
                return [
                    'success' => true,
                    'user_id' => $row['id'],
                    'role' => $row['role'],
                    'two_factor_secret' => $row['two_factor_secret']
                ];
            }
        }
        
        return ['success' => false];
    }

    public function verify2FA($userId, $code) {
        $query = "SELECT two_factor_secret FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->google2fa->verifyKey($row['two_factor_secret'], $code);
        }
        
        return false;
    }

    public function createSession($userId) {
        $sessionToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "INSERT INTO sessions (user_id, session_token, expires_at) 
                 VALUES (:user_id, :session_token, :expires_at)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":session_token", $sessionToken);
        $stmt->bindParam(":expires_at", $expiresAt);
        
        if($stmt->execute()) {
            return $sessionToken;
        }
        
        return false;
    }

    public function getUserRole($userId) {
        $query = "SELECT role FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['role'];
        }
        
        return null;
    }

    public function isAdmin($userId) {
        return $this->getUserRole($userId) === 'admin';
    }

    public function getSecretKey($userId) {
        $query = "SELECT two_factor_secret FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['two_factor_secret'];
        }
        
        return null;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?> 