<?php
    session_start();
    include '../config.php';

    // Ensure the admin is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }



// // Set default records per page (10) and allow user selection
// if (isset($_GET['records_per_page'])) {
//     $_SESSION['records_per_page'] = (int)$_GET['records_per_page'];
// }
// $records_per_page = isset($_SESSION['records_per_page']) ? $_SESSION['records_per_page'] : 10;

// $selected_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
// $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
// $offset = ($current_page - 1) * $records_per_page;

// // Fetch total record count for pagination
// $count_query = "SELECT COUNT(*) as total FROM feedback_results";
// if (!empty($selected_user)) {
//     $count_query .= " WHERE user_id = '" . $conn->real_escape_string($selected_user) . "'";
// }
// $total_records = $conn->query($count_query)->fetch_assoc()['total'];
// $total_pages = ceil($total_records / $records_per_page);

// // Fetch feedback history with filtering and pagination
// $query_history = "SELECT users.username, feedback_results.score, feedback_results.percentage, 
//                 feedback_results.rating, feedback_results.submitted_at 
//                 FROM feedback_results 
//                 JOIN users ON feedback_results.user_id = users.id";
// if (!empty($selected_user)) {
//     $query_history .= " WHERE feedback_results.user_id = ?";
// }
// $query_history .= " ORDER BY feedback_results.submitted_at DESC LIMIT ? OFFSET ?";

// $stmt = $conn->prepare($query_history);
// if (!empty($selected_user)) {
//     $stmt->bind_param("iii", $selected_user, $records_per_page, $offset);
// } else {
//     $stmt->bind_param("ii", $records_per_page, $offset);
// }
// $stmt->execute();
// $result_history = $stmt->get_result();
// $feedback_history = $result_history->fetch_all(MYSQLI_ASSOC);
// $stmt->close();



    // Fetch all users' feedback history with usernames
    $query_history = "SELECT users.username, feedback_results.score, feedback_results.percentage, feedback_results.rating, feedback_results.submitted_at 
                    FROM feedback_results 
                    JOIN users ON feedback_results.user_id = users.id 
                    ORDER BY feedback_results.submitted_at DESC";
    $result_history = $conn->query($query_history);
    $feedback_history = $result_history->fetch_all(MYSQLI_ASSOC);

    // Fetch only non-admin users for dropdown filter
    $query_users = "SELECT id, username FROM users WHERE role != 'admin' ORDER BY username ASC";
    $result_users = $conn->query($query_users);
    $users = $result_users->fetch_all(MYSQLI_ASSOC);

    // Handle filtering based on selected user
    $selected_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';
    $query_history = "SELECT users.username, feedback_results.score, feedback_results.percentage, feedback_results.rating, feedback_results.submitted_at 
                    FROM feedback_results 
                    JOIN users ON feedback_results.user_id = users.id";

    if (!empty($selected_user)) {
        $query_history .= " WHERE feedback_results.user_id = ?";
    }
    $query_history .= " ORDER BY feedback_results.submitted_at DESC";

    $stmt = $conn->prepare($query_history);
    if (!empty($selected_user)) {
        $stmt->bind_param("i", $selected_user);
    }
    $stmt->execute();
    $result_history = $stmt->get_result();
    $feedback_history = $result_history->fetch_all(MYSQLI_ASSOC);


    $title_page = "Admin - VR Mental Wellness";
    include('../includes/admin/header_navbar.php')
?>
    <div class="container mt-5 pt-4">
        <h2 class="text-center text-white">User Feedback Results</h2>
        <div class="table-container mx-auto">
            <!-- Select User Form -->
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-6">
                    <label for="userSelect" class="form-label"><strong>Select User:</strong></label>
                    <select name="user_id" id="userSelect" class="form-select" onchange="this.form.submit()">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($selected_user == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
           <!-- <div class="col-md-6">
                    <label for="recordsPerPage" class="form-label"><strong>Records per page:</strong></label>
                    <select name="records_per_page" id="recordsPerPage" class="form-select" onchange="this.form.submit()">
                        <?php foreach ([5, 10, 25, 50, 100] as $option) : ?>
                            <option value="<?php echo $option; ?>" <?php echo ($records_per_page == $option) ? 'selected' : ''; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div> -->
            </div>
        </form>
        
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Username</th>
                        <th>Date</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedback_history as $feedback) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['username']); ?></td>
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
                </tbody>
            </table>

                    <!-- Pagination Buttons -->
       <!-- <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?user_id=<?php echo $selected_user; ?>&records_per_page=<?php echo $records_per_page; ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
                    </li>
                    <li class="page-item disabled"><a class="page-link">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></a></li>
                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?user_id=<?php echo $selected_user; ?>&records_per_page=<?php echo $records_per_page; ?>&page=<?php echo $current_page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav> -->
        </div>
    </div>

<!-- Feedback Graph - Admin Dashboard -->

<div class="container mt-4">
        <div class="card shadow-lg p-3">
            <div class="card-body">
                <h2 class="text-center text-primary fw-bold">User Feedback Results in Graph</h2>
                <div class="chart-container" style="position: relative; height: 50vh; width: 100%;">
                    <canvas id="feedbackChart"></canvas>
                </div>
            </div>
        </div>
    </div>

        <?php
        // Fetch feedback from all users (for admin)
        $query_results = "
            SELECT users.username, feedback_results.score, feedback_results.percentage, 
                feedback_results.rating, feedback_results.submitted_at 
            FROM feedback_results 
            JOIN users ON feedback_results.user_id = users.id
            ORDER BY feedback_results.submitted_at DESC";
        $stmt_results = $conn->prepare($query_results);
        $stmt_results->execute();
        $result_results = $stmt_results->get_result();
        $feedback_results = $result_results->fetch_all(MYSQLI_ASSOC);
        $stmt_results->close();

        // Convert dates to Asia/Manila timezone and include username
        $formatted_dates = array_map(function($feedback) {
            $dt = new DateTime($feedback['submitted_at'], new DateTimeZone('America/Los_Angeles'));
            $dt->setTimezone(new DateTimeZone('Asia/Manila'));
            return $feedback['username'] . " - " . $dt->format("F j, Y g:i A"); // Show username + date
        }, $feedback_results);
        ?>

<script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('feedbackChart').getContext('2d');

            const feedbackData = {
                labels: <?php echo json_encode($formatted_dates); ?>, // Labels: Username + Date
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

            console.log("Debugging Chart Data:", feedbackData); // Debugging

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
<?php include('../includes/admin/footer.php')?>