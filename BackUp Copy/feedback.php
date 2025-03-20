<?php
    session_start();
    include 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch feedback questions from database
    $query_questions = "SELECT id, question FROM feedback_questions ORDER BY id ASC";
    $result_questions = $conn->query($query_questions);
    $questions = $result_questions->fetch_all(MYSQLI_ASSOC);

    // Handle feedback form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $total_score = 0;
        $num_questions = count($questions);
        
        foreach ($questions as $question) {
            $qid = $question['id'];
            $score = isset($_POST["q$qid"]) ? (int)$_POST["q$qid"] : 1;
            $total_score += $score;
        }
        
        $percentage = ($total_score / ($num_questions * 5)) * 100;
        
        if ($percentage >= 90) {
            $rating = "Excellent";
        } elseif ($percentage >= 80) {
            $rating = "Good";
        } elseif ($percentage >= 60) {
            $rating = "Average";
        } elseif ($percentage >= 21) {
            $rating = "Needs Improvement";
        } else {
            $rating = "Unsatisfactory";
        } 
        
        $stmt = $conn->prepare("INSERT INTO feedback_results (user_id, score, percentage, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $user_id, $total_score, $percentage, $rating);
        $stmt->execute();
        $stmt->close();

        $_SESSION['feedback_submitted'] = true; // Set session variable
        header("Location: feedback.php"); // Redirect to prevent form resubmission
        exit();

    }

    // Fetch Username
    $query_username = "SELECT username FROM users WHERE id = ?";
    $stmt_username = $conn->prepare($query_username);
    $stmt_username->bind_param("i", $user_id);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $username = $result_username->fetch_assoc();
    $stmt_username->close();


    $title_page = "Feedback - VR Mental Wellness";
    include('includes/header_navbar.php')
?>
    <div class="container mt-5 pt-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-white">Feedback Form</h2>
            <p class="lead text-white">Take Your Feedback, <strong><?php echo strtoupper(htmlspecialchars($username['username'])); ?>!</strong></p>
        </div>

        <div class="card-container">
            <form method="post">
                <p class="fw-bold text-center">Rate the following</p>
                <?php foreach ($questions as $question): ?>
                    <div class="mb-3">
                        <label class="form-label"> <?php echo htmlspecialchars($question['question']); ?> </label>
                        <select name="q<?php echo $question['id']; ?>" class="form-select" required>
                            <option value=""><p>Choose your Rate</p></option>
                            <option value="1">1<p>. Unsatisfactory</p></option>
                            <option value="2">2<p>. Needs Improvement</p></option>
                            <option value="3">3<p>. Average</p></option>
                            <option value="4">4<p>. Good</p></option>
                            <option value="5">5<p>. Excellent</p></option>
                        </select>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-submit"> 🚀 Submit</button>
            </form>
                <div id="notification" class="alert alert-success mt-3 <?php echo isset($_SESSION['feedback_submitted']) ? '' : 'd-none'; ?>" role="alert">
                    Thank You for your feedback! <a href="feedback_result.php" class="btn btn-sm btn-info ms-2">View Results</a>
                </div>
                <?php unset($_SESSION['feedback_submitted']); // Remove session variable after displaying ?>

        </div>
    </div>
<?php include('includes/footer.php')?>

