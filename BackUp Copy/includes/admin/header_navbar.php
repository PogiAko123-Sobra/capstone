<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
       <!-- General CSS File -->
    <link href="../css/style.css" rel="stylesheet">
    <title>
        <?php if(isset($title_page)) {
            echo "$title_page";
            }
        ?>
    </title>
    <style>
        body { 
            background: url("../Background/bg.jpg") no-repeat center/cover fixed; 
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top" style="background: url('../Background/bg.jpg') no-repeat center/cover fixed;">
        <div class="container-fluid">
            <h4 class="text-white mb-0 fw-bolder text-uppercase border-bottom border-info pb-1">ADMIN DASHBOARD</h4>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-link fw-bold text-light d-flex align-items-center gap-2 position-relative btn btn-outline-info shadow-sm px-3 py-2"><a href="../admin/index.php" class="nav-link fw-bold text-light d-flex align-items-center">Dashboard</a></li>
                    <li class="nav-link fw-bold text-light d-flex align-items-center gap-2 position-relative btn btn-outline-info shadow-sm px-3 py-2"><a href="../admin/activity_log.php" class="nav-link fw-bold text-light d-flex align-items-center">User's Activity Log</a></li>
                    <li class="nav-link fw-bold text-light d-flex align-items-center gap-2 position-relative btn btn-outline-info shadow-sm px-3 py-2"><a href="../admin/admin_feedback.php" class="nav-link fw-bold text-light d-flex align-items-center">User's Daily Feedback Result</a></li>
                    <li class="nav-link fw-bold text-light d-flex align-items-center gap-2 position-relative btn btn-outline-info shadow-sm px-3 py-2"><a href="../logout.php" class="nav-link fw-bold text-light d-flex align-items-center">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav><br><br><br><hr style="color: white; ">