<?php 
     $title_page = "Login - VR Mental Wellness";
    include('includes/login_register/header.php'); 
?>
    <div class="container">

        <?php
            session_start();
            include 'config.php';

            // Redirect if already logged in
            if (isset($_SESSION['user_id']) && isset($_SESSION['Role'])) {
                header("Location: " . ($_SESSION['Role'] == 'Admin' ? "admin/index.php" : "index.php"));
                exit();
            }

            // Initialize login attempts if not set
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
            }

            $lockout_time = 60; // 1 minute in seconds

            // Check if the user is locked out
            if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
                $remaining_time = ceil(($_SESSION['lockout_time'] - time()) / 60);
                echo '<div class="alert alert-danger text-center p-3 rounded shadow-lg">
                        <h5 class="fw-bold">🚫 Too Many Failed Attempts</h5>
                        <p>Please try again in ' . $remaining_time . ' minutes.</p>
                    </div>';
            } else {
                // Reset login attempts after lockout time has passed
                if (isset($_SESSION['lockout_time']) && time() >= $_SESSION['lockout_time']) {
                    $_SESSION['login_attempts'] = 0;
                    unset($_SESSION['lockout_time']);
                }

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $username = trim($_POST['username']);
                    $password = trim($_POST['password']);
                    $recaptchaResponse = $_POST['g-recaptcha-response'];

                    // reCAPTCHA Secret Key
                    $secretKey = "6Lc20PIqAAAAACgdA1-5ZvK30I0N5GHfYsagSTkl";

                    // Verify reCAPTCHA
                    $verifyURL = "https://www.google.com/recaptcha/api/siteverify";
                    $data = [
                        'secret' => $secretKey,
                        'response' => $recaptchaResponse,
                        'remoteip' => $_SERVER['REMOTE_ADDR']
                    ];
                    $options = [
                        'http' => [
                            'method' => 'POST',
                            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                            'content' => http_build_query($data)
                        ]
                    ];
                    $context = stream_context_create($options);
                    $captchaResult = json_decode(file_get_contents($verifyURL, false, $context), true);

                    // Check reCAPTCHA validation
                    if (!$captchaResult['success']) {
                        echo '<div class="alert alert-danger text-center p-3 rounded shadow-lg">
                                <h5 class="fw-bold">⚠️ reCAPTCHA Verification Failed</h5>
                                <p>Please complete the reCAPTCHA challenge.</p>
                            </div>';
                    } else {
                        // Proceed with username & password validation after reCAPTCHA is successful
                        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows == 1) {
                            $row = $result->fetch_assoc();
                            if (password_verify($password, $row['password'])) {
                                $_SESSION['user_id'] = $row['id'];
                                $_SESSION['Role'] = $row['role'];

                                // Reset login attempts on success
                                $_SESSION['login_attempts'] = 0;
                                unset($_SESSION['lockout_time']);

                                header("Location: " . ($row['Role'] == 'Admin' ? "admin/index.php" : "index.php"));
                                 // Get User Activity Log Upon Logging in
                                $stmt = $conn->prepare("INSERT INTO activity_log (user_id, username, action) VALUES (?, ?, 'Login')");
                                $stmt->bind_param("is", $row['id'], $row['username']);
                                $stmt->execute();
                                exit();
                            } else {
                                $_SESSION['login_attempts']++;
                            }
                        } else {
                            $_SESSION['login_attempts']++;
                        }

                        // If failed 3 times, set lockout time
                        if ($_SESSION['login_attempts'] >= 3) {
                            $_SESSION['lockout_time'] = time() + $lockout_time;
                            echo '<div class="alert alert-danger text-center p-3 rounded shadow-lg">
                                    <h5 class="fw-bold">🚫 Too Many Failed Attempts</h5>
                                    <p>Please try again in 1 minute.</p>
                                </div>';
                        } else {
                            echo '<div class="alert alert-danger text-center p-3 rounded shadow-lg">
                                    <h5 class="fw-bold">❌ Incorrect Credentials</h5>
                                    <p>Attempt ' . $_SESSION['login_attempts'] . ' of 3.</p>
                                </div>';
                        }
                    }
                }
            }
        ?>

        <h2 class="text-primary fw-bold mb-4">Welcome Back!</h2>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control bg-transparent border p-2" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control bg-transparent border p-2" placeholder="Password" required>
            </div>
            <center><div class="g-recaptcha" data-sitekey="6Lc20PIqAAAAALR_-QCum-ZeAi9DRu7xNoxgCkp1"></div></center><br>
            <button type="submit" class="btn btn-primary w-100">Login</button>
            <hr>
            <a href="register.php" class="btn btn-secondary w-100">Register</a>
        </form>
    </div>
</body>
</html>
