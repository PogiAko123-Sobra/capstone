<?php
    session_start();
    include 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch feedback results
    $query_results = "SELECT score, percentage, rating, submitted_at FROM feedback_results WHERE user_id = ? ORDER BY id DESC";
    $stmt_results = $conn->prepare($query_results);
    $stmt_results->bind_param("i", $user_id);
    $stmt_results->execute();
    $result_results = $stmt_results->get_result();
    $feedback_results = $result_results->fetch_all(MYSQLI_ASSOC);
    $stmt_results->close();

    // Fetch Username
    $query_username = "SELECT username FROM users WHERE id = ?";
    $stmt_username = $conn->prepare($query_username);
    $stmt_username->bind_param("i", $user_id);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $username = $result_username->fetch_assoc();
    $stmt_username->close();

    $title_page = "Feedback Result - VR Mental Wellness";
    include('includes/header_navbar.php')
?>
    <div class="container mt-5 pt-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-white">Feedback Results Table</h2>
            <p class="lead text-white">View Your Result Below, <strong><?php echo strtoupper(htmlspecialchars($username['username'])); ?>!</strong></p>
        </div>

        <div class="table-container mx-auto table-responsive">
            <table class="progress-table table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($feedback_results)) : ?>
                        <?php foreach ($feedback_results as $feedback) : ?>
                            <tr>
                            
                                <td><?php
                                // Use this only when already Hosted //
                                $date = new DateTime($feedback['submitted_at'], new DateTimeZone('America/Los_Angeles')); // UTC time from DB
                                $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                                echo $date->format("F j, Y g:i A"); // Display with timezone abbreviation
                                ?></td>
                                <td><?php echo htmlspecialchars($feedback['score']); ?></td>
                                <td><?php echo round($feedback['percentage'], 2); ?>%</td>
                                <td><?php echo htmlspecialchars($feedback['rating']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-muted"><strong>No Feedback Found!</strong></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table><br>
          <button class="btn-submit"><a href="feedback.php" style="text-decoration: none; color: white;">Retake Feedback</a></button>
        </div>
    </div>
</div>    
                    <!-- Graph -->
            <div class="container mt-4">
                <div class="card shadow-lg p-3">
                    <div class="card-body">
                        <h2 class="text-center text-primary fw-bold">Feedback Results in Graph</h2>
                        <div class="chart-container" style="position: relative; height: 50vh; width: 100%;">
                            <canvas id="feedbackChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php
        $formatted_dates = array_map(function($date) {
            // Convert from America/Los_Angeles to Asia/Manila
            $dt = new DateTime($date, new DateTimeZone('America/Los_Angeles'));
            $dt->setTimezone(new DateTimeZone('Asia/Manila'));
            return $dt->format("F j, Y g:i A"); // Output in desired format
        }, array_column($feedback_results, 'submitted_at'));
        ?>

        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('feedbackChart').getContext('2d');

            const feedbackData = {
                labels: <?php echo json_encode($formatted_dates); ?>, // Already converted in PHP
                datasets: [
                    {
                        label: 'Score',
                        data: <?php echo json_encode(array_column($feedback_results, 'score')); ?>,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Percentage',
                        data: <?php echo json_encode(array_column($feedback_results, 'percentage')); ?>,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            };

            new Chart(ctx, {
                type: 'line',
                data: feedbackData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: {
                            ticks: { autoSkip: true, maxTicksLimit: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: "Score & Percentage" }
                        }
                    }
                }
            });
        });
        </script>

<?php include('includes/footer.php')?>