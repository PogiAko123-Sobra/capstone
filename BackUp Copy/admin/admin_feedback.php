<?php
    session_start();
    include '../config.php';

    // Ensure the admin is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

// Handle delete request before any HTML is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']); // Ensure ID is an integer
    $delete_query = "DELETE FROM feedback WHERE id = $delete_id";
    
    if ($conn->query($delete_query) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit;
}

// Timeframes for stats
$timeframes = [
    'Today' => "WHERE DATE(feedback_date) = CURDATE()",
    'This Week' => "WHERE YEARWEEK(feedback_date) = YEARWEEK(CURDATE())",
    'This Month' => "WHERE MONTH(feedback_date) = MONTH(CURDATE()) AND YEAR(feedback_date) = YEAR(CURDATE())",
    'Previous Month' => "WHERE MONTH(feedback_date) = MONTH(CURDATE() - INTERVAL 1 MONTH) 
                         AND YEAR(feedback_date) = YEAR(CURDATE() - INTERVAL 1 MONTH)",
    'This Year' => "WHERE YEAR(feedback_date) = YEAR(CURDATE())"
];

// Store statistics
$stats = [];
foreach ($timeframes as $label => $condition) {
    $query = "SELECT 
                 SUM(session_duration) AS total_duration, 
                 AVG(stress_level_before) AS avg_before, 
                 AVG(stress_level_after) AS avg_after, 
                 (AVG(stress_level_before) - AVG(stress_level_after)) AS avg_reduction,
                 MAX(feedback_date) AS latest_feedback
              FROM feedback $condition";
    $res = $conn->query($query);
    $data = $res->fetch_assoc();
    
    $stats[$label] = [
        'total_duration' => round($data['total_duration'] ?? 0, 2),
        'avg_before' => round($data['avg_before'] ?? 0, 2),
        'avg_after' => round($data['avg_after'] ?? 0, 2),
        'avg_reduction' => round($data['avg_reduction'] ?? 0, 2),
        'latest_feedback' => $data['latest_feedback']
    ];
}

// Fetch all feedback for the table & graph
$feedback_query = "SELECT id, session_duration, stress_level_before, stress_level_after, feedback_date FROM feedback ORDER BY feedback_date DESC";
$feedback_result = $conn->query($feedback_query);


// Prepare data arrays for JavaScript graph
$dates = [];
$durations = [];
$stressBefore = [];
$stressAfter = [];
$stressReduction = [];

// Reset pointer for the feedback result
$graph_data = $conn->query("SELECT id, session_duration, stress_level_before, stress_level_after, feedback_date FROM feedback ORDER BY feedback_date ASC");
while ($row = $graph_data->fetch_assoc()) {
    $dates[] = "'" . date("M d, H:i", strtotime($row['feedback_date'])) . "'";
    $durations[] = $row['session_duration'];
    $stressBefore[] = $row['stress_level_before'];
    $stressAfter[] = $row['stress_level_after'];
    $stressReduction[] = $row['stress_level_before'] - $row['stress_level_after'];
}

// get total session_duration
$query = "SELECT COUNT(*) AS total_sessions FROM feedback WHERE session_duration IS NOT NULL";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$totalSessions = $row['total_sessions'];

$conn->close();

    $title_page = "Feedback Results - VR Mental Wellness";
    include('../includes/admin/header_navbar.php')
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VR Monitoring Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.12/sweetalert2.min.js"></script>
      <!-- Bar Graph Script link-->
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      
    <link rel="stylesheet" href="../css/2show.css">

    <?php
    // PHP code would be here in the actual implementation
    // We're including placeholder data for the demo
    ?>

    <header class="dashboard-header">
        <div class="container">
            <div class="text-center">
                <h1><i class="fas fa-vr-cardboard me-2"></i> VR Monitoring Dashboard</h1>
                <p class="subtitle mt-2">Track user experience, stress reduction, and usage patterns</p>
            </div>
        </div>
    </header>
    
    <div class="container mb-5">
        <!-- Main stats card -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Today's Usage</h5>
                    <div class="stat-value" id="todayDuration"><?php echo $stats['Today']['total_duration']; ?> min</div>
                    <div class="stat-label">Total minutes in VR</div>
                    
                    <div class="stat-row">
                        <div class="label">Total Sessions</div>
                        <div class="value"><?php echo $totalSessions; ?></div>
                    </div>

                    <div class="stat-row">
                        <div class="label">Last Entry</div>
                        <div class="value"><?php
                                $date = new DateTime($stats['Today']['latest_feedback'], new DateTimeZone('America/Los_Angeles')); // Assuming UTC time from DB
                                $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                                echo $date->format("F j, Y g:i A"); // Display formatted date
                            ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <div class="stat-icon">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <h5>Stress Before</h5>
                    <div class="stat-value" id="stressBefore"><?php echo $stats['Today']['avg_before']; ?> pts</div>
                    <div class="stat-label">Average stress level</div>
                    
                    <!-- <div class="stress-change negative">
                        <span class="icon"><i class="fas fa-arrow-up"></i></span>
                        <span>0.5 pts from last week</span>
                    </div> -->
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h5>Stress After</h5>
                    <div class="stat-value" id="stressAfter"><?php echo $stats['Today']['avg_after']; ?> pts</div>
                    <div class="stat-label">Average stress level</div>
                    <!--                     
                    <div class="stress-change positive">
                        <span class="icon"><i class="fas fa-arrow-down"></i></span>
                        <span>0.8 pts from last week</span>
                    </div> -->
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Stress Reduction</h5>
                    <div class="stat-value" id="stressReduction"><?php echo $stats['Today']['avg_reduction']; ?> pts</div>
                    <div class="stat-label">Average points reduced</div>
                    
                    <!-- <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 36%;" aria-valuenow="36" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-end mt-1">
                        <small>36% effectiveness</small>
                    </div> -->
                </div>
            </div>
        </div>
        
        <!-- Toggle for additional stats -->
        <div class="toggle-stats-container text-center">
            <button class="btn-toggle" onclick="toggleStats()">
                <span id="toggleText">Show More Statistics</span>
                <i class="fas fa-chevron-down ms-2" id="toggleIcon"></i>
            </button>
        </div>
        
        <!-- Additional stats (hidden by default) -->
        <div id="extraStats" class="row" style="display: none;">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <h5>This Week</h5>
                    <div class="stat-row">
                        <div class="label">Total VR Time</div>
                        <div class="value"><?php echo $data['total_duration']; ?> min</div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress Before</div>
                        <div class="value"><?php echo $data['avg_before']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress After</div>
                        <div class="value"><?php echo $data['avg_after']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Reduction</div>
                        <div class="value"><?php echo $data['avg_reduction']; ?> pts</div>
                    </div>
                    <div class="date-badge mt-2">
                        <i class="far fa-calendar-alt me-1"></i> Last: <?php
                            $date = new DateTime($data['latest_feedback'], new DateTimeZone('America/Los_Angeles')); // Assuming UTC time from DB
                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                            echo $date->format("F j, Y g:i A"); // Display formatted date
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <h5>This Month</h5>
                    <div class="stat-row">
                        <div class="label">Total VR Time</div>
                        <div class="value"><?php echo $data['total_duration']; ?> min</div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress Before</div>
                        <div class="value"><?php echo $data['avg_before']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress After</div>
                        <div class="value"><?php echo $data['avg_after']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Reduction</div>
                        <div class="value"><?php echo $data['avg_reduction']; ?> pts</div>
                    </div>
                    <div class="date-badge mt-2">
                        <i class="far fa-calendar-alt me-1"></i> Last: <?php
                            $date = new DateTime($data['latest_feedback'], new DateTimeZone('America/Los_Angeles')); // Assuming UTC time from DB
                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                            echo $date->format("F j, Y g:i A"); // Display formatted date
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <h5>Previous Month</h5>
                    <div class="stat-row">
                        <div class="label">Total VR Time</div>
                        <div class="value"><?php echo $data['total_duration']; ?> min</div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress Before</div>
                        <div class="value"><?php echo $data['avg_before']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress After</div>
                        <div class="value"><?php echo $data['avg_after']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Reduction</div>
                        <div class="value"><?php echo $data['avg_reduction']; ?> pts</div>
                    </div>
                    <div class="date-badge mt-2">
                        <i class="far fa-calendar-alt me-1"></i> Last: <?php
                            $date = new DateTime($data['latest_feedback'], new DateTimeZone('America/Los_Angeles')); // Assuming UTC time from DB
                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                            echo $date->format("F j, Y g:i A"); // Display formatted date
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="stat-card">
                    <div class="card-pattern"></div>
                    <h5>This Year</h5>
                    <div class="stat-row">
                        <div class="label">Total VR Time</div>
                        <div class="value"><?php echo $data['total_duration']; ?> min</div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress Before</div>
                        <div class="value"><?php echo $data['avg_before']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Stress After</div>
                        <div class="value"><?php echo $data['avg_after']; ?></div>
                    </div>
                    <div class="stat-row">
                        <div class="label">Reduction</div>
                        <div class="value"><?php echo $data['avg_reduction']; ?> pts</div>
                    </div>
                    <div class="date-badge mt-2">
                        <i class="far fa-calendar-alt me-1"></i> Last: <?php
                            $date = new DateTime($data['latest_feedback'], new DateTimeZone('America/Los_Angeles')); // Assuming UTC time from DB
                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                            echo $date->format("F j, Y g:i A"); // Display formatted date
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Section -->
        <div class="chart-section mt-4">
            <h3 class="section-title">
                <i class="fas fa-chart-line"></i>
                Stress Reduction & VR Usage Trends
            </h3>
            
            <div class="chart-tabs">
                <div class="chart-tab active" data-period="week">Last 7 Days</div>
                <div class="chart-tab" data-period="month">Last 30 Days</div>
                <div class="chart-tab" data-period="year">This Year</div>
            </div>
            
            <div class="chart-container">
                <canvas id="vrChart"></canvas>
            </div>
        </div>
        
        <!-- Table Section -->
        <div class="table-container mt-4">
            <h3 class="section-title">
                <i class="fas fa-clipboard-list"></i>
                All Feedback Entries
            </h3>
            
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Duration</th>
                            <th>Stress Before</th>
                            <th>Stress After</th>
                            <th>Change</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if ($feedback_result->num_rows > 0) {
                        while ($row = $feedback_result->fetch_assoc()): 
                    ?>
                         <tr id="row-<?php echo $row['id']; ?>">
                            <td>
                                <span class="date-badge">
                                    <i class="far fa-calendar-alt me-1"></i> <?php
                                            $date = new DateTime($row['feedback_date'], new DateTimeZone('America/Los_Angeles')); // Assuming stored in UTC
                                            $date->setTimezone(new DateTimeZone('Asia/Manila')); // Convert to Philippine Time
                                            echo $date->format("F j, Y g:i A"); // Display formatted date
                                        ?>
                                </span>
                            </td>
                            <td><strong><?php echo $row['session_duration']; ?></strong></td>
                            <td>
                                <span class="badge-stress" style="background-color: rgba(247, 37, 133, 0.2); color: #f72585;">
                                    <?php echo $row['stress_level_before']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-stress" style="background-color: rgba(76, 201, 240, 0.2); color: #4cc9f0;">
                                    <?php echo $row['stress_level_after']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="stress-change positive">
                                    <i class="fas fa-arrow-down"></i> <?php echo $data['avg_reduction']; ?> pts
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-action btn-danger btn-sm" onclick="deleteFeedback(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php 
                        endwhile;
                    } else {
                        echo '<tr><td colspan="5" class="text-center">No feedback entries found</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="text-center mt-4 mb-4">
            <a href="feedback_form.php" class="btn btn-action btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Submit New Feedback
            </a>
        </div>
    </div>

                <!-- Graph -->
    <script>
        // Toggle Extra Stats Function
        function toggleStats() {
            const extraStats = document.getElementById("extraStats");
            const toggleText = document.getElementById("toggleText");
            const toggleIcon = document.getElementById("toggleIcon");
            
            if (extraStats.style.display === "none") {
                extraStats.style.display = "flex";
                toggleText.textContent = "Hide Statistics";
                toggleIcon.className = "fas fa-chevron-up ms-2";
            } else {
                extraStats.style.display = "none";
                toggleText.textContent = "Show More Statistics";
                toggleIcon.className = "fas fa-chevron-down ms-2";
            }
        }
        
        // Delete Feedback Function
      
        function deleteFeedback(id) {
            Swal.fire({
                title: "Are you sure?",
                text: "This feedback entry will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to delete the feedback
                    const formData = new FormData();
                    formData.append('delete_id', id);
                    
                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.getElementById("row-" + id).remove();
                            Swal.fire("Deleted!", "The feedback has been removed.", "success");
                        } else {
                            Swal.fire("Error!", "Could not delete the feedback: " + data.message, "error");
                        }
                    })
                    .catch(error => {
                        Swal.fire("Error!", "An error occurred while deleting.", "error");
                        console.error('Error:', error);
                    });
                }
            });
        }
        
        // Update stats after deletion (simplified for demo)
        function updateStatsAfterDelete() {
            // Simulate recalculation of stats
            const currentValue = parseFloat(document.getElementById("stressReduction").innerText);
            const newValue = (currentValue - 0.1).toFixed(1);
            
            document.getElementById("stressReduction").innerText = newValue;
            
            // Update other stats as needed in a real implementation
        }
        
        
        // Chart initialization || Bar Graph// 
        document.addEventListener('DOMContentLoaded', function() {
            // data for the chart
            const weeklyData = {
                labels: [<?php echo implode(",", $dates); ?>],
                datasets: [
                    {
                        label: 'Before VR',
                        data: [<?php echo implode(",", $stressBefore); ?>],
                        borderColor: '#f72585',
                        backgroundColor: 'rgba(247, 37, 133, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'After VR',
                        data: [<?php echo implode(",", $stressAfter); ?>],
                        borderColor: '#4cc9f0',
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'VR Usage (min)',
                        data: [<?php echo implode(",", $durations); ?>],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            };
            
            const monthlyData = {
                labels: [<?php echo implode(",", $dates); ?>],
                datasets: [
                    {
                        label: 'Before VR',
                        data: [<?php echo implode(",", $stressBefore); ?>],
                        borderColor: '#f72585',
                        backgroundColor: 'rgba(247, 37, 133, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'After VR',
                        data: [<?php echo implode(",", $stressAfter); ?>],
                        borderColor: '#4cc9f0',
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'VR Usage (min)',
                        data: [<?php echo implode(",", $durations); ?>],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            };
            
            const yearlyData = {
                labels: [<?php echo implode(",", $dates); ?>],
                datasets: [
                    {
                        label: 'Before VR',
                        data: [<?php echo implode(",", $stressBefore); ?>],
                        borderColor: '#f72585',
                        backgroundColor: 'rgba(247, 37, 133, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'After VR',
                        data: [<?php echo implode(",", $stressAfter); ?>],
                        borderColor: '#4cc9f0',
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'VR Usage (min)',
                        data: [<?php echo implode(",", $durations); ?>],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            };
            
            // Create the chart
            const ctx = document.getElementById('vrChart').getContext('2d');
            const vrChart = new Chart(ctx, {
                type: 'line',
                data: weeklyData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#212529',
                            bodyColor: '#212529',
                            borderColor: 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            bodyFont: {
                                family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.yAxisID === 'y1') {
                                        label += context.parsed.y + ' min';
                                    } else {
                                        label += context.parsed.y + ' / 10';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 10,
                            ticks: {
                                stepSize: 2
                            },
                            title: {
                                display: true,
                                text: 'Stress Level (0-10)',
                                color: '#6c757d',
                                font: {
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y1: {
                            min: 0,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'VR Usage (minutes)',
                                color: '#6c757d',
                                font: {
                                    family: "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
                                    size: 12
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    }
                }
            });
            
            // Handle chart tab switching
            document.querySelectorAll('.chart-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    // Update active tab
                    document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update chart data based on selected period
                    const period = this.getAttribute('data-period');
                    let chartData;
                    
                    switch(period) {
                        case 'week':
                            chartData = weeklyData;
                            break;
                        case 'month':
                            chartData = monthlyData;
                            break;
                        case 'year':
                            chartData = yearlyData;
                            break;
                        default:
                            chartData = weeklyData;
                    }
                    
                    // Update chart with new data
                    vrChart.data = chartData;
                    vrChart.update();
                });
            });
            
            // Simulate real-time data updates
            setInterval(() => {
                const randomChange = (Math.random() * 0.4 - 0.2).toFixed(1);
                const currentStressBefore = parseFloat(document.getElementById('stressBefore').innerText);
                const newStressBefore = Math.min(10, Math.max(0, (currentStressBefore + parseFloat(randomChange)).toFixed(1)));
                
                document.getElementById('stressBefore').innerText = newStressBefore;
                
                // Update other values as needed
                updateDerivedStats();
            }, 60000); // Update every minute
        });
        
        // Update derived statistics based on primary values
        function updateDerivedStats() {
            const stressBefore = parseFloat(document.getElementById('stressBefore').innerText);
            const stressAfter = parseFloat(document.getElementById('stressAfter').innerText);
            const reduction = (stressBefore - stressAfter).toFixed(1);
            
            document.getElementById('stressReduction').innerText = reduction;
            
            // Update progress bar
            const reductionPercentage = (reduction / 10 * 100).toFixed(0);
            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = reductionPercentage + '%';
            progressBar.setAttribute('aria-valuenow', reductionPercentage);
            
            document.querySelector('.progress + .text-end small').innerText = 
                reductionPercentage + '% effectiveness';
        }
    </script>
    
<?php include('../includes/admin/footer.php')?>