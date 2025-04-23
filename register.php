<?php
// Start a new session to store user data
session_start();
// Include the Auth class for authentication functionality
require_once 'classes/Auth.php';

// Create a new instance of the Auth class
$auth = new Auth();
// Initialize error and success message variables
$error = '';
$success = '';

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from POST request with fallback to empty string if not set
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    
    // Validate that passwords match
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Attempt to register the new user
        if ($auth->register($username, $email, $password, $role)) {
            // Query to get the newly created user's ID
            $query = "SELECT id FROM users WHERE username = :username";
            $stmt = $auth->getConnection()->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Store user ID and username in session for 2FA setup
            $_SESSION['setup_user_id'] = $user['id'];
            $_SESSION['setup_username'] = $username;
            
            // Redirect to 2FA setup page
            header('Location: setup-2fa.php');
            exit();
        } else {
            $error = 'Username or email already exists';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML meta tags and title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Secure Auth System</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styling for the registration page */
        body {
            background-color: #f8f9fa;
        }
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <!-- Registration form title -->
            <h2 class="text-center mb-4">Register</h2>
            <!-- Display error message if registration failed -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Display success message if registration succeeded -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <!-- Registration form -->
            <form method="POST" action="">
                <!-- Username input field -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <!-- Email input field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <!-- Password input field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <!-- Confirm password input field -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <!-- Role selection dropdown -->
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-control" id="role" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <!-- Submit button -->
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <!-- Link to login page -->
            <div class="text-center mt-3">
                <a href="index.php">Already have an account? Login</a>
            </div>
        </div>
    </div>
    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 