<?php 
    session_start();
    include 'config.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch Username
    $query_username = "SELECT username FROM users WHERE id = ?";
    $stmt_username = $conn->prepare($query_username);
    $stmt_username->bind_param("i", $user_id);
    $stmt_username->execute();
    $result_username = $stmt_username->get_result();
    $username = $result_username->fetch_assoc();
    $stmt_username->close();
    
    $title_page = "Daily Feedback - VR Mental Wellness";
    include('includes/header_navbar.php')
?>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/3form.css">
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    </style>

<div id="stars" class="parallax-stars"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card my-4">
                    <div class="card-header text-center">
                        <div class="floating-vr mb-3">
                            <i class="fas fa-vr-cardboard vr-icon"></i>
                        </div>
                        <h2 class="mb-1 fw-bold">VR Experience Feedback</h2>
                        <p class="mb-0 text-light">Step into the future - share your experience</p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <form action="submit_feedback.php" method="post">
                            <div class="mb-4">
                            
                            <div class="mb-4">
                                <label for="session_duration" class="form-label">
                                    <span class="form-icon"><i class="fas fa-clock"></i></span>
                                    Time Spent in VR
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-hourglass-half text-primary"></i></span>
                                    <input type="number" class="form-control" id="session_duration" name="session_duration" min="1" placeholder="Duration in minutes" required>
                                    <span class="input-group-text bg-white">minutes</span>
                                </div>
                            </div>
                            
                            <div class="section-divider">
                                <span>Stress Measurement</span>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="stress_before" class="form-label">
                                        <span class="form-icon"><i class="fas fa-heart-pulse"></i></span>
                                        Stress Before VR
                                    </label>
                                    <div class="slider-container">
                                        <div id="stress_before_badge" class="slider-value" style="left: 50%;">5</div>
                                        <input type="range" class="form-range" id="stress_before" name="stress_before" min="1" max="10" value="5" oninput="updateSlider('stress_before')">
                                        <div class="slider-labels">
                                            <small class="text-success">Relaxed</small>
                                            <small class="text-danger">Stressed</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="stress_after" class="form-label">
                                        <span class="form-icon"><i class="fas fa-heart-pulse"></i></span>
                                        Stress After VR
                                    </label>
                                    <div class="slider-container">
                                        <div id="stress_after_badge" class="slider-value" style="left: 50%;">5</div>
                                        <input type="range" class="form-range" id="stress_after" name="stress_after" min="1" max="10" value="5" oninput="updateSlider('stress_after')">
                                        <div class="slider-labels">
                                            <small class="text-success">Relaxed</small>
                                            <small class="text-danger">Stressed</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="section-divider">
                                <span>Your Experience</span>
                            </div>
                            
                            <div class="mb-4">
                                <label for="mood" class="form-label">
                                    <span class="form-icon"><i class="fas fa-face-smile"></i></span>
                                    How do you feel after using VR?
                                </label>
                                
                                <div class="mood-selector" id="mood-selector">
                                    <div class="mood-item" data-value="1">
                                        <i class="far fa-face-sad-tear mood-emoji"></i>
                                        <span class="mood-text">Terrible</span>
                                    </div>
                                    <div class="mood-item" data-value="2">
                                        <i class="far fa-face-frown mood-emoji"></i>
                                        <span class="mood-text">Bad</span>
                                    </div>
                                    <div class="mood-item" data-value="3">
                                        <i class="far fa-face-meh mood-emoji"></i>
                                        <span class="mood-text">Neutral</span>
                                    </div>
                                    <div class="mood-item" data-value="4">
                                        <i class="far fa-face-smile mood-emoji"></i>
                                        <span class="mood-text">Good</span>
                                    </div>
                                    <div class="mood-item" data-value="5">
                                        <i class="far fa-face-grin-stars mood-emoji"></i>
                                        <span class="mood-text">Amazing</span>
                                    </div>
                                </div>
                                
                                <textarea class="form-control" id="mood" name="mood" rows="3" placeholder="Describe how you feel after the VR experience..." required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label for="improvement" class="form-label">
                                    <span class="form-icon"><i class="fas fa-lightbulb"></i></span>
                                    Suggestions for Improvement
                                </label>
                                <textarea class="form-control" id="improvement" name="improvement" rows="3" placeholder="Share your ideas to make the experience better..."></textarea>
                            </div>
                            
                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-primary btn-lg px-5 py-3">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and supporting JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Create stars background
        document.addEventListener('DOMContentLoaded', function() {
            const starsContainer = document.getElementById('stars');
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.width = `${Math.random() * 3 + 1}px`;
                star.style.height = star.style.width;
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                star.style.animationDelay = `${Math.random() * 5}s`;
                starsContainer.appendChild(star);
            }
            
            // Mood selector functionality
            const moodItems = document.querySelectorAll('.mood-item');
            const moodInput = document.getElementById('mood');
            const moodTexts = [
                "I felt quite uncomfortable and anxious during the VR experience...",
                "The VR experience wasn't enjoyable. I felt...",
                "The VR experience was okay, but could be improved by...",
                "I enjoyed the VR experience. It made me feel...",
                "The VR experience was absolutely amazing! I felt completely immersed and..."
            ];
            
            moodItems.forEach(item => {
                item.addEventListener('click', () => {
                    // Remove selected class from all items
                    moodItems.forEach(i => i.classList.remove('selected'));
                    // Add selected class to clicked item
                    item.classList.add('selected');
                    // Set placeholder based on selection
                    const value = item.getAttribute('data-value');
                    moodInput.placeholder = moodTexts[value - 1];
                    
                    // Add subtle animation to mood item
                    item.style.animation = 'none';
                    setTimeout(() => {
                        item.style.animation = 'pulse 1s';
                    }, 10);
                });
            });
        });
        
        // Update slider position and value
        function updateSlider(id) {
            const slider = document.getElementById(id);
            const badge = document.getElementById(`${id}_badge`);
            const value = slider.value;
            const percent = (value - slider.min) / (slider.max - slider.min) * 100;
            
            badge.textContent = value;
            badge.style.left = `calc(${percent}% + (${8 - percent * 0.15}px))`;
            
            // Update badge color based on value
            if (value <= 3) {
                badge.style.background = "#198754"; // Green for low stress
            } else if (value <= 7) {
                badge.style.background = "#fd7e14"; // Orange for medium stress
            } else {
                badge.style.background = "#dc3545"; // Red for high stress
            }
        }
        
        // Initialize sliders
        updateSlider('stress_before');
        updateSlider('stress_after');
        
        // Add parallax effect to stars
        window.addEventListener('mousemove', function(e) {
            const stars = document.getElementById('stars');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            stars.style.transform = `translate(${x * 20}px, ${y * 20}px)`;
        });
    </script>
<?php include('includes/footer.php')?>
