<?php
require_once "config/database.php";

// Admin credentials
$username = "admin";
$password = "admin123"; // This will be hashed
$role = "admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// First, delete existing admin account if it exists
$delete_sql = "DELETE FROM users WHERE username = ?";
if ($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
    mysqli_stmt_bind_param($delete_stmt, "s", $username);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    echo "Deleted existing admin account if any.<br>";
}

// Insert new admin user
$sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin account created successfully!<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: " . $password . "<br>";
        echo "Role: " . $role . "<br>";
        echo "<br>Please delete this file after creating the admin account for security reasons.";
    } else {
        echo "Error creating admin account: " . mysqli_error($conn);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing insert statement: " . mysqli_error($conn);
}

// Show the current admin account details
echo "<br><br>Current admin account details:<br>";
$result = mysqli_query($conn, "SELECT user_id, username, role FROM users WHERE username = 'admin'");
if ($row = mysqli_fetch_assoc($result)) {
    echo "ID: " . $row['user_id'] . "<br>";
    echo "Username: " . $row['username'] . "<br>";
    echo "Role: " . $row['role'] . "<br>";
}

mysqli_close($conn);
?> 