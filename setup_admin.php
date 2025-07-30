<?php
require_once "config/database.php";

// Check if admin user already exists
$sql = "SELECT user_id FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Create admin user
    $username = "admin";
    $password = "admin123"; // Default password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = "admin";
    
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin user created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        echo "Please change the password after first login.";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Admin user already exists.";
}
?> 