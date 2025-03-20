<?php
session_start();
include '../config.php';

// Get selected user ID from GET request
$selected_user = isset($_GET['user_id']) ? $_GET['user_id'] : '';

// Set default records per page (10) and allow user selection
if (isset($_GET['records_per_page'])) {
    $_SESSION['records_per_page'] = (int)$_GET['records_per_page'];
}
$records_per_page = isset($_SESSION['records_per_page']) ? $_SESSION['records_per_page'] : 10;

$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Fetch total record count for pagination
$count_query = "SELECT COUNT(*) as total FROM activity_log";
if (!empty($selected_user)) {
    $count_query .= " WHERE user_id = '" . $conn->real_escape_string($selected_user) . "'";
}
$total_records = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch activity logs based on selected user with pagination
$query = "SELECT * FROM activity_log";
if (!empty($selected_user)) {
    $query .= " WHERE user_id = '" . $conn->real_escape_string($selected_user) . "'";
}
$query .= " ORDER BY timestamp DESC LIMIT $records_per_page OFFSET $offset";
$activity_log = $conn->query($query);

// Fetch only non-admin users for dropdown filter
$query_users = "SELECT id, username FROM users WHERE role != 'admin' ORDER BY username ASC";
$result_users = $conn->query($query_users);
$users = $result_users->fetch_all(MYSQLI_ASSOC);

$title_page = "User Activity Logs - VR Mental Wellness";
include('../includes/admin/header_navbar.php');
?>

<div class="container mt-5 pt-4">
    <h2 class="text-center text-white">User Activity Logs</h2>
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
                <div class="col-md-6">
                    <label for="recordsPerPage" class="form-label"><strong>Records per page:</strong></label>
                    <select name="records_per_page" id="recordsPerPage" class="form-select" onchange="this.form.submit()">
                        <?php foreach ([5, 10, 25, 50, 100] as $option) : ?>
                            <option value="<?php echo $option; ?>" <?php echo ($records_per_page == $option) ? 'selected' : ''; ?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Username</th>
                    <th>Action</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = $activity_log->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td><?php
                            // Use this only when already Hosted //
                            $date = new DateTime($activity['timestamp'], new DateTimeZone('America/Los_Angeles')); // UTC time from DB
                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                            echo $date->format("F j, Y g:i A"); // Display with timezone abbreviation
                        ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination Buttons -->
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?user_id=<?php echo $selected_user; ?>&records_per_page=<?php echo $records_per_page; ?>&page=<?php echo $current_page - 1; ?>">Previous</a>
                </li>
                <li class="page-item disabled"><a class="page-link">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></a></li>
                <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?user_id=<?php echo $selected_user; ?>&records_per_page=<?php echo $records_per_page; ?>&page=<?php echo $current_page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php include('../includes/admin/footer.php'); ?>
