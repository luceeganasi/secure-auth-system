<?php
// Start a new session to store user data
session_start();
// Include the Auth class for authentication functionality
require_once 'classes/Auth.php';

// Create a new instance of the Auth class
$auth = new Auth();
// Initialize error message variable
$error = '';

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get username and password from POST data, with fallback to empty string if not set
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Attempt to login using the Auth class
    $result = $auth->login($username, $password);
    
    if ($result['success']) {
        // If login successful, store user ID in session
        $_SESSION['user_id'] = $result['user_id'];
        // Set flag indicating 2FA verification is required
        $_SESSION['two_factor_required'] = true;
        // Redirect to 2FA verification page
        header('Location: verify-2fa.php');
        exit();
    } else {
        // If login failed, set error message
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML meta tags and title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Secure Auth System</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styling for the login page */
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Login form title -->
            <h2 class="text-center mb-4">Login</h2>
            <!-- Display error message if login failed -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Login form -->
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <!-- Link to registration page -->
            <div class="text-center mt-3">
                <a href="register.php">Don't have an account? Register</a>
            </div>
        </div>
    </div>
    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 