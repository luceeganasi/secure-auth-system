<?php
session_start();
require_once 'classes/Auth.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['two_factor_required'])) {
    header('Location: index.php');
    exit();
}

$auth = new Auth();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    if ($auth->verify2FA($_SESSION['user_id'], $code)) {
        $sessionToken = $auth->createSession($_SESSION['user_id']);
        if ($sessionToken) {
            $_SESSION['session_token'] = $sessionToken;
            unset($_SESSION['two_factor_required']);
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $error = 'Invalid 2FA code';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Secure Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            <h2 class="text-center mb-4">Two-Factor Authentication</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <p class="text-center mb-4">Please enter the 6-digit code from your authenticator app</p>
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" class="form-control text-center" id="code" name="code" 
                           maxlength="6" pattern="[0-9]{6}" required 
                           placeholder="Enter 6-digit code">
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 