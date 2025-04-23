<?php
// Start the session to access session data
session_start();
// Destroy all session data
session_destroy();
// Redirect to the login page
header('Location: index.php');
// Ensure no further code is executed
exit();
?> 