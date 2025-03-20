<?php 
    session_start();
    include 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch Username
    $query_username = "SELECT username, Role FROM users WHERE id = ?";
    $stmt_username = $conn->prepare($query_username);
    $stmt_username->bind_param("i", $user_id);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $username = $result_username->fetch_assoc();
    $stmt_username->close();

    $title_page = "Home - VR Mental Wellness";
    include('includes/header_navbar.php')
?>
<style>

</style>

    <!-- Hero Section -->
    <section id="hero" class="hero section d-flex align-items-center text-center text-lg-start dark-background">
        <div class="container">
            <div class="row align-items-center gy-4">
                <!-- Left Content -->
                <div class="col-lg-6 order-2 order-lg-1" data-aos="fade-right">
                    <h1 class="fw-bold text-light">
                        Leveraging Virtual Reality for the <span class="text-highlight">Mental Wellness</span> of Guidance Counselors
                    </h1>
                    <p class="lead text-light">Record and Monitor your Mental Wellness</p>
                    <div class="d-flex justify-content-center justify-content-lg-start gap-3">
                        <a href="#about" class="btn btn-primary btn-lg">Get Started</a>
                        <a href="#" class="btn btn-outline-light btn-lg d-flex align-items-center">
                            <i class="bi bi-play-circle fs-4 me-2"></i> Watch Video
                        </a>
                    </div>
                </div>
                <!-- Right Image -->
                <div class="col-lg-6 order-1 order-lg-2 text-center" data-aos="fade-left" data-aos-delay="200">
                    <img src="Images/6.png" class="img-fluid animated" alt="Hero Image">
                </div>
            </div>
        </div>
    </section>
<?php include('includes/footer.php')?>