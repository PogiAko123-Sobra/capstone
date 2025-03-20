<?php
    session_start();
    include 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

// Get data from form submission
// $user_id = $_POST['user_id'];
$session_duration = $_POST['session_duration'];
$stress_before = $_POST['stress_before'];
$stress_after = $_POST['stress_after'];
$mood = $_POST['mood'];
$improvement = $_POST['improvement'];

// Insert data into the database
$sql = "INSERT INTO feedback (user_id, session_duration, stress_level_before, stress_level_after, mood, improvement) 
        VALUES ('$user_id', '$session_duration', '$stress_before', '$stress_after', '$mood', '$improvement')";

if ($conn->query($sql) === TRUE) {
    echo "Feedback submitted successfully! <br>";
    echo "<a href='feedback_form.php'>Submit Another Feedback</a> | ";
    echo "<a href='index.php'>Go Back</a>";

} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
