<?php
session_start();
include 'config.php'; // Ensure this connects to your database

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch the username of the user
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $username = $row['username'];

        // Insert logout record into activity_log
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, username, action) VALUES (?, ?, 'logout')");
        $stmt->bind_param("is", $user_id, $username);
        $stmt->execute();
        $stmt->close();
    }
}

// Destroy the session and redirect to login
session_destroy();
header("Location: login.php");
exit();
?>
