<?php
session_start();
include 'config.php'; // Ensure database connection

// Allow CORS for Unity requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight request (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method. Expected POST. Received: " . $_SERVER['REQUEST_METHOD']]);
    exit();
}

// Read JSON input from Unity request
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validate if user_id exists
if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "No valid user ID received"]);
    exit();
}

$user_id = intval($data['user_id']);

// Check if user exists
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];

    // Insert into the Unity activity log table
    $stmt = $conn->prepare("INSERT INTO unity_activity_log (user_id, username, action) VALUES (?, ?, 'Opened App')");
    $stmt->bind_param("is", $user_id, $username);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Activity logged successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
?>
