<?php 
    $title_page = "Register Admin - VR Mental Wellness";
    include('../../includes/login_register/header.php'); 
?>

<div class="container">
    
        <?php
            include '../../config.php';

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);
                
                // Hash the password securely
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Check if the username already exists
                $username = mysqli_real_escape_string($conn, $username);
                $checkQuery = "SELECT id FROM users WHERE username = '$username'";
                $result = mysqli_query($conn, $checkQuery);

                if (mysqli_num_rows($result) > 0) {
                    echo '<div class="alert alert-danger text-center p-3 rounded">❌ Username already exists! <br> <a href="../../admin/register/index.php" class="btn btn-danger mt-2">Try Again</a></div>';
                } else {
                    // Insert new user with a default role
                    $sql = "INSERT INTO users (username, password, Role) VALUES ('$username', '$hashedPassword', 'Admin')";
                    if (mysqli_query($conn, $sql)) {
                        echo '<div class="alert alert-success text-center p-3 rounded">🎉 Registration Successful! <br> <a href="../../login.php" class="btn btn-primary mt-2">Go to Login</a></div>';
                    } else {
                        echo '<div class="alert alert-danger text-center p-3 rounded">❌ Registration Failed! <br> Error: ' . mysqli_error($conn) . ' <br> <a href="register.php" class="btn btn-danger mt-2">Try Again</a></div>';
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
            <input type="password" name="password" class="form-control bg-transparent border p-2" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register as Admin</button>
    </form>
    <div class="text-center mt-3">
        <p>Already have an account? <a href="../../login.php" class="text-primary fw-bold">Login here</a></p>
    </div>
</div>

</body>
</html>

