<?php
session_start();
$_SESSION = array(); // Clear session variables
session_unset();
session_destroy(); // Destroy the session

// Delete session cookie if exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: ../login.php");
exit();
?>
