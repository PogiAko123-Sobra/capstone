
<?php 
    $title_page = "Register - VR Mental Wellness";
    include('includes/login_register/header.php'); 
?>
<div class="container">
    
        <?php
            include 'config.php';

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = trim($_POST['password']);
                
                // Password validation
                if (strlen($password) < 8 || strlen($password) > 20 || 
                    !preg_match('/[A-Z]/', $password) || 
                    !preg_match('/[a-z]/', $password) || 
                    !preg_match('/[0-9]/', $password) ||
                    !preg_match('/[\W_]/', $password)) {
                    echo '<div class="alert alert-danger text-center p-3 rounded">❌ Password must be 8-20 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.<br><a href="register.php" class="btn btn-danger mt-2">Try Again</a></div>';
                    exit();
                }
                
                // Hash the password securely
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Validate email format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo '<div class="alert alert-danger text-center p-3 rounded">❌ Invalid email format! <br> <a href="register.php" class="btn btn-danger mt-2">Try Again</a></div>';
                } else {
                    // Check if the username or email already exists
                    $username = mysqli_real_escape_string($conn, $username);
                    $email = mysqli_real_escape_string($conn, $email);
                    $checkQuery = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
                    $result = mysqli_query($conn, $checkQuery);

                    if (mysqli_num_rows($result) > 0) {
                        echo '<div class="alert alert-danger text-center p-3 rounded">❌ Username or Email already exists! <br> <a href="register.php" class="btn btn-danger mt-2">Try Again</a></div>';
                    } else {
                        // Insert new user with email for recovery
                        $sql = "INSERT INTO users (username, email, password, Role) VALUES ('$username', '$email', '$hashedPassword', 'User')";
                        if (mysqli_query($conn, $sql)) {
                            echo '<div class="alert alert-success text-center p-3 rounded">🎉 Registration Successful! <br> <a href="login.php" class="btn btn-primary mt-2">Go to Login</a></div>';
                        } else {
                            echo '<div class="alert alert-danger text-center p-3 rounded">❌ Registration Failed! <br> Error: ' . mysqli_error($conn) . ' <br> <a href="register.php" class="btn btn-danger mt-2">Try Again</a></div>';
                        }
                    }
                }
                // Close the database connection
                mysqli_close($conn);
            }
        ?>

    <h1 class="text-primary text-center fw-bold mb-4">Registration</h1>
    <form method="POST">
        <div class="mb-3">
            <input type="text" name="username" class="form-control bg-transparent border p-2" placeholder="Username" required>
        </div>
        <div class="mb-3">
            <input type="email" name="email" class="form-control bg-transparent border p-2" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <input type="password" name="password" id="password" class="form-control bg-transparent border p-2" placeholder="Password" required minlength="8" maxlength="20" 
            pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,20}$" 
            title="Password must be 8-20 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.">
            <div id="password-strength" class="password-strength"></div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <div class="text-center mt-3">
        <p>Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
    </div>
</div>

<script>
    document.getElementById('password').addEventListener('input', function () {
        let password = this.value;
        let strengthText = "";
        let strengthIndicator = document.getElementById('password-strength');
        
        if (password.length < 8) {
            strengthText = "❌ Too short";
        } else if (password.length > 20) {
            strengthText = "❌ Too long";
        } else if (!/[A-Z]/.test(password)) {
            strengthText = "⚠️ Must contain at least one uppercase letter";
        } else if (!/[a-z]/.test(password)) {
            strengthText = "⚠️ Must contain at least one lowercase letter";
        } else if (!/[0-9]/.test(password)) {
            strengthText = "⚠️ Must contain at least one number";
        } else if (!/[\W_]/.test(password)) {
            strengthText = "⚠️ Must contain at least one special character";
        } else {
            strengthText = "✅ Strong password";
        }
        
        strengthIndicator.innerHTML = strengthText;
    });
</script>

</body>
</html>