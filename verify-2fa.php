<?php
// Start a new session to access stored user data
session_start();
// Include the Auth class for authentication functionality
require_once 'classes/Auth.php';

// Check if user is logged in and 2FA is required
if (!isset($_SESSION['user_id']) || !isset($_SESSION['two_factor_required'])) {
    // Redirect to login page if not authenticated
    header('Location: index.php');
    exit();
}

// Create a new instance of the Auth class
$auth = new Auth();
// Initialize error message variable
$error = '';

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get 2FA code from POST data with fallback to empty string if not set
    $code = $_POST['code'] ?? '';
    
    // Verify the 2FA code
    if ($auth->verify2FA($_SESSION['user_id'], $code)) {
        // Create a new session token for the user
        $sessionToken = $auth->createSession($_SESSION['user_id']);
        if ($sessionToken) {
            // Store session token and remove 2FA requirement
            $_SESSION['session_token'] = $sessionToken;
            unset($_SESSION['two_factor_required']);
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        }
    } else {
        // Set error message if 2FA verification failed
        $error = 'Invalid 2FA code';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML meta tags and title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Secure Auth System</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styling for the 2FA verification page */
        body {
            background-color: #f8f9fa;
        }
        .verify-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-container">
            <!-- 2FA verification form title -->
            <h2 class="text-center mb-4">Two-Factor Authentication</h2>
            <!-- Display error message if verification failed -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Instructions for the user -->
            <p class="text-center mb-4">Please enter the 6-digit code from your authenticator app</p>
            <!-- 2FA verification form -->
            <form method="POST" action="">
                <div class="mb-3">
                    <!-- 2FA code input field with validation -->
                    <input type="text" class="form-control text-center" id="code" name="code" 
                           maxlength="6" pattern="[0-9]{6}" required 
                           placeholder="Enter 6-digit code">
                </div>
                <!-- Submit button -->
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 