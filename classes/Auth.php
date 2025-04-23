<?php
// Include database configuration and autoloader
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Import Google2FA library for two-factor authentication
use PragmaRX\Google2FA\Google2FA;

class Auth {
    // Database connection and Google2FA instance
    private $conn;
    private $google2fa;

    // Constructor to initialize database connection and Google2FA
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->google2fa = new Google2FA();
    }

    // Register a new user with username, email, password, and role
    public function register($username, $email, $password, $role = 'user') {
        // Check if user already exists
        $query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        // Return false if user already exists
        if($stmt->rowCount() > 0) {
            return false;
        }

        // Generate 2FA secret key for the new user
        $twoFactorSecret = $this->google2fa->generateSecretKey();

        // Hash the password for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into database
        $query = "INSERT INTO users (username, email, password, two_factor_secret, role) 
                 VALUES (:username, :email, :password, :two_factor_secret, :role)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":two_factor_secret", $twoFactorSecret);
        $stmt->bindParam(":role", $role);

        // Return true if registration successful
        return $stmt->execute();
    }

    // Authenticate user with username and password
    public function login($username, $password) {
        // Get user data from database
        $query = "SELECT id, password, two_factor_secret, role FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        // Check if user exists
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if(password_verify($password, $row['password'])) {
                // Return user data if authentication successful
                return [
                    'success' => true,
                    'user_id' => $row['id'],
                    'role' => $row['role'],
                    'two_factor_secret' => $row['two_factor_secret']
                ];
            }
        }
        
        // Return failure if authentication failed
        return ['success' => false];
    }

    // Verify two-factor authentication code
    public function verify2FA($userId, $code) {
        // Get user's 2FA secret from database
        $query = "SELECT two_factor_secret FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        // Verify the code using Google2FA
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->google2fa->verifyKey($row['two_factor_secret'], $code);
        }
        
        return false;
    }

    // Create a new session for authenticated user
    public function createSession($userId) {
        // Generate random session token
        $sessionToken = bin2hex(random_bytes(32));
        // Set session expiration to 1 hour from now
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store session in database
        $query = "INSERT INTO sessions (user_id, session_token, expires_at) 
                 VALUES (:user_id, :session_token, :expires_at)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":session_token", $sessionToken);
        $stmt->bindParam(":expires_at", $expiresAt);
        
        // Return session token if successful
        if($stmt->execute()) {
            return $sessionToken;
        }
        
        return false;
    }

    // Get user's role from database
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

    // Check if user is an admin
    public function isAdmin($userId) {
        return $this->getUserRole($userId) === 'admin';
    }

    // Get user's 2FA secret key
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

    // Get database connection
    public function getConnection() {
        return $this->conn;
    }
}
?> 