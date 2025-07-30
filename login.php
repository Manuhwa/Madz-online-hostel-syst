<?php
                            // Example login script
session_start();

require_once 'config/database.php';

// Initialize variables
$username = $password = "";
$login_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get submitted username and password
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate credentials
    if (empty($username) || empty($password)) {
        $login_err = "Please enter both username and password.";
    } else {
        // Retrieve user record
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["username"] = $user['username'];
                $_SESSION["role"] = $user['role'];
                
                // Redirect user based on role
                if ($user['role'] === "admin") {
                    header("location: admin/dashboard.php");
                } else {
                    header("location: student/dashboard.php");
                }
                exit;
            } else {
                $login_err = "Invalid password.";
            }
        } else {
            $login_err = "User not found.";
        }
    }
    
    // If there was an error, store it in session and redirect back to login page
    if (!empty($login_err)) {
        $_SESSION['error'] = $login_err;
        header("location: index.php");
        exit;
    }
}
?>

