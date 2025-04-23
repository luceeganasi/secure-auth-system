<?php
// Start a new session to access stored user data
session_start();
// Include the Auth class for authentication functionality
require_once 'classes/Auth.php';

// Check if user is in the setup process
if (!isset($_SESSION['setup_user_id'])) {
    // Redirect to registration page if not in setup process
    header('Location: register.php');
    exit();
}

// Create a new instance of the Auth class
$auth = new Auth();
// Initialize error and success message variables
$error = '';
$success = '';

// Get the secret key for 2FA setup
$secretKey = $auth->getSecretKey($_SESSION['setup_user_id']);

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get verification code from POST data
    $code = $_POST['code'] ?? '';
    
    // Verify the 2FA code
    if ($auth->verify2FA($_SESSION['setup_user_id'], $code)) {
        $success = '2FA setup successful!';
        // Clear setup session variables
        unset($_SESSION['setup_user_id']);
        unset($_SESSION['setup_username']);
        // Redirect to login page
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid verification code';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic HTML meta tags and title -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Two-Factor Authentication - Secure Auth System</title>
    <!-- Include Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom styling for the 2FA setup page */
        body {
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        /* Styling for setup steps */
        .step {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        /* Styling for step numbers */
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            background: #007bff;
            color: white;
            border-radius: 50%;
            margin-right: 10px;
        }
        /* Styling for the secret key display */
        .secret-key {
            font-family: monospace;
            font-size: 1.2em;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-align: center;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="setup-container">
            <!-- Page title -->
            <h2 class="text-center mb-4">Setup Two-Factor Authentication</h2>
            <!-- Display error message if verification failed -->
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <!-- Display success message if setup completed -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Information about 2FA -->
            <div class="alert alert-info">
                <h5 class="alert-heading">Why Two-Factor Authentication?</h5>
                <p>Two-factor authentication adds an extra layer of security to your account. Even if someone knows your password, they won't be able to access your account without your authentication code.</p>
            </div>

            <!-- Step 1: Install Google Authenticator -->
            <div class="step">
                <span class="step-number">1</span>
                <strong>Install Google Authenticator</strong>
                <p>Download and install the Google Authenticator app on your mobile device:</p>
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" 
                       class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-google-play"></i> Android
                    </a>
                    <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" 
                       class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-apple"></i> iOS
                    </a>
                </div>
            </div>

            <!-- Step 2: Add Account Manually -->
            <div class="step">
                <span class="step-number">2</span>
                <strong>Add Account Manually</strong>
                <p>In the Google Authenticator app:</p>
                <ol>
                    <li>Tap the "+" button to add a new account</li>
                    <li>Select "Enter a setup key"</li>
                    <li>Enter your account name (e.g., "Auth System")</li>
                    <li>Enter this secret key:</li>
                </ol>
                <!-- Display the secret key for manual entry -->
                <div class="secret-key mb-3">
                    <?php echo chunk_split($secretKey, 4, ' '); ?>
                </div>
                <p class="text-muted">Type: Time-based</p>
            </div>

            <!-- Step 3: Verify Setup -->
            <div class="step">
                <span class="step-number">3</span>
                <strong>Verify Setup</strong>
                <p>Enter the 6-digit code from Google Authenticator to verify your setup:</p>
                <form method="POST" action="">
                    <div class="mb-3">
                        <!-- 2FA code input field with validation -->
                        <input type="text" class="form-control text-center" id="code" name="code" 
                               maxlength="6" pattern="[0-9]{6}" required 
                               placeholder="Enter 6-digit code"
                               style="font-size: 1.5rem; letter-spacing: 5px;">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify and Complete Setup</button>
                </form>
            </div>

            <!-- Important notes about 2FA -->
            <div class="alert alert-warning mt-4">
                <h5 class="alert-heading">Important Notes:</h5>
                <ul class="mb-0">
                    <li>Keep your Google Authenticator app secure</li>
                    <li>Make sure to backup your recovery codes</li>
                    <li>If you lose access to your authenticator app, contact support</li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Include Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus the code input field when page loads
        document.getElementById('code').focus();
        
        // Auto-submit form when 6 digits are entered
        document.getElementById('code').addEventListener('input', function(e) {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    </script>
</body>
</html> 